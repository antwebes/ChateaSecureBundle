<?php
/**
 * User: José Ramón Fernandez Leis
 * Email: jdeveloper.inxenio@gmail.com
 * Date: 29/07/15
 * Time: 12:09
 */

namespace Ant\Bundle\ChateaSecureBundle\Tests\Security\Firewall;

use Ant\Bundle\ChateaSecureBundle\Security\Firewall\AutologinListener;
use Ant\Bundle\ChateaSecureBundle\Security\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class AutologinListenerTest extends \PHPUnit_Framework_TestCase
{
    private $autologinListener;
    private $tokenStorage;
    private $authenticationManager;
    private $event;

    protected function setUp()
    {
        parent::setUp();

        $this->tokenStorage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $this->authenticationManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $this->authorizationChecker = $this->getMock("Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface");
        $this->autologinListener = new AutologinListener($this->tokenStorage, $this->authenticationManager, $this->authorizationChecker);
        $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequest', 'setResponse'))
            ->getMock();
    }

    public function testHandleWithNoAutologin()
    {
        $request = new Request();

        $this->mockCall($this->tokenStorage, 'getToken', null, $this->never());
        $this->mockCall($this->tokenStorage, 'setToken', null, $this->never());
        $this->mockCall($this->authorizationChecker, 'isGranted', null, $this->never());
        $this->mockCall($this->authenticationManager, 'authenticate', null, $this->never());
        $this->mockCall($this->event, 'getRequest', $request);
        $this->mockCall($this->event, 'setResponse', null, $this->never());

        $this->autologinListener->handle($this->event);
    }


    public function testHandleWithAutologin()
    {
        $validAccessToken ='validAccessToken';
        $request = new Request(array('autologin' => $validAccessToken));
        $authToken = $this->getAuthToken();

        $this->mockCall($this->tokenStorage, 'getToken', null);
        $this->mockCall($this->tokenStorage, 'setToken', null, $this->once(), $authToken);
        $this->mockCall($this->authorizationChecker, 'isGranted', null, $this->never());
        $this->mockCall($this->authenticationManager, 'authenticate', $authToken, $this->once(), $this->getAccessTokenAsserter($validAccessToken));
        $this->mockCall($this->event, 'getRequest', $request);
        $this->mockCall($this->event, 'setResponse', null, $this->once());

        $this->autologinListener->handle($this->event);

        $this->assertFalse($request->query->has('autologin'));
    }

    public function testHandleWithAutologinWithInvalidAccessToken()
    {
        $invalidAccessToken ='invalidAccessToken';
        $request = new Request(array('autologin' => $invalidAccessToken));
        $authToken = $this->getAuthToken();

        $exception = new BadCredentialsException();

        $this->mockCall($this->tokenStorage, 'getToken', null);
        $this->mockCall($this->tokenStorage, 'setToken', null, $this->never());
        $this->mockCall($this->authorizationChecker, 'isGranted', null, $this->never());
        $this->mockCall($this->authenticationManager, 'authenticate', $this->throwException($exception), $this->once(), $this->getAccessTokenAsserter($invalidAccessToken));
        $this->mockCall($this->event, 'getRequest', $request);
        $this->mockCall($this->event, 'setResponse', null, $this->once());

        $this->autologinListener->handle($this->event);

        $this->assertFalse($request->query->has('autologin'));
    }

    public function testHandleWithAutologinAndAllreadyLogedin()
    {
        $validAccessToken ='validAccessToken';
        $request = new Request(array('autologin' => $validAccessToken));
        $authToken = $this->getAuthToken($validAccessToken);

        $this->mockCall($this->tokenStorage, 'getToken', $authToken, $this->any());
        $this->mockCall($this->tokenStorage, 'setToken', null, $this->never());
        $this->mockCall($this->authorizationChecker, 'isGranted', true);
        $this->mockCall($this->authenticationManager, 'authenticate', null, $this->never());
        $this->mockCall($this->event, 'getRequest', $request);
        $this->mockCall($this->event, 'setResponse', null, $this->once());

        $this->autologinListener->handle($this->event);

        $this->assertFalse($request->query->has('autologin'));
    }

    public function testHandleWithAutologinAndAllreadyLogedinButDifferentToken()
    {
        $validAccessToken = 'validAccessToken';
        $request = new Request(array('autologin' => $validAccessToken));
        $authToken = $this->getAuthToken('oldValidAccessToken');

        $this->mockCall($this->tokenStorage, 'getToken', $authToken, $this->any());
        $this->mockCall($this->tokenStorage, 'setToken', null, $this->once());
        $this->mockCall($this->authorizationChecker, 'isGranted', true);
        $this->mockCall($this->authenticationManager, 'authenticate', $authToken, $this->once(), $this->getAccessTokenAsserter($validAccessToken));
        $this->mockCall($this->event, 'getRequest', $request);
        $this->mockCall($this->event, 'setResponse', null, $this->once());

        $this->autologinListener->handle($this->event);

        $this->assertFalse($request->query->has('autologin'));
    }

    private function mockCall($object, $method, $return = null, $when = null, $with = null)
    {
        if($when === null){
            $when = $this->any();
        }

        $mock = $object
            ->expects($when)
            ->method($method);

        if($with !== null){
            $mock->with($with);
        }

        if($return !== null){
            if(!($return instanceof \PHPUnit_Framework_MockObject_Stub)){
                $return = $this->returnValue($return);
            }

            $mock->will($return);
        }
    }

    private function getAuthToken($accessToken = '321IUKKL')
    {
        $user = new User(2, 'username', $accessToken, '12HHIIK', true, 'password', 3600, array('role_1'));
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        return $token;
    }

    private function getAccessTokenAsserter($accessToken)
    {
        return $this->callback(function($token) use($accessToken){
            return $token->getAccessToken() == $accessToken;
        });
    }
}