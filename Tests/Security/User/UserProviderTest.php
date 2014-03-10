<?php
namespace Ant\Bundle\ChateaSecureBundle\Security\User;

use Ant\Bundle\ChateaSecureBundle\Security\User\UserProvider;
use Ant\Bundle\ChateaSecureBundle\Security\User\User;
use Guzzle\Http\Exception\BadResponseException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    private $authenticator;
    private $userProvider;

    public function setUp()
    {
        $this->authenticator = $this->getMockBuilder('Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\HttpAdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->userProvider = new UserProvider($this->authenticator);
    }

    public function testLoadUserThrowsExceptionIfUsernameIsNotGiven()
    {
        try {
            $this->userProvider->loadUser('', 'password');
        } catch (\InvalidArgumentException $e) {
            if($e->getMessage() == 'The username cannot be empty.')
                return;
        }

        $this->fail('Expected to raise an InvalidArgumentException');
    }

    public function testLoadUserThrowsExceptionIfPasswordIsNotGiven()
    {
        try {
            $this->userProvider->loadUser('username', '');
        } catch (\InvalidArgumentException $e) {
            if($e->getMessage() == 'The password cannot be empty.')
                return;
        }

        $this->fail('Expected to raise an InvalidArgumentException');
    }

    public function testLoadUser()
    {
        $responseToken = $this->getResponseToken();
        $user = $this->getExpectedUser();

        $this->authenticator
            ->expects($this->once())
            ->method('withUserCredentials')
            ->with('username', 'password')
            ->will($this->returnValue($responseToken));

        $this->assertEquals($user, $this->userProvider->loadUser('username', 'password'));
    }

    public function testLoadUserAuthenticatedByEmail()
    {
        $responseToken = $this->getResponseToken();
        $user = $this->getExpectedUser();

        $this->authenticator
            ->expects($this->once())
            ->method('withUserCredentials')
            ->with('user@mycuteemail.com', 'password')
            ->will($this->returnValue($responseToken));

        $this->assertEquals($user, $this->userProvider->loadUser('user@mycuteemail.com', 'password'));
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserWhenAuthenticatorThrowsUsernameNotFoundException()
    {
        $exception = $this->getUsernameNotFoundException();

        $this->authenticator
            ->expects($this->once())
            ->method('withUserCredentials')
            ->with('username', 'password')
            ->will($this->throwException($exception));

        $this->userProvider->loadUser('username', 'password');
    }

    public function testLoadUserWhenApiIsDown()
    {
        $exception = $this->getApiException();

        $this->authenticator
            ->expects($this->once())
            ->method('withUserCredentials')
            ->with('username', 'password')
            ->will($this->throwException($exception));

        try{
            $this->userProvider->loadUser('username', 'password');
        }catch(BadCredentialsException $e){
            $this->assertEquals('Authentication service down', $e->getMessage());
            return;
        }

        $this->fail('Expected to raise an BadCredentialsException with message: Service down');
    }


    public function testRefreshUser()
    {
        $this->authenticator
            ->expects($this->never())
            ->method('withRefreshToken');

        $user = $this->getExpectedMockedUser();

        $this->assertEquals($user, $this->userProvider->refreshUser($user));
    }

    public function testRefreshUserWithExpiredAccessToken()
    {
        $responseToken = $this->getResponseToken();
        $this->authenticator
            ->expects($this->once())
            ->method('withRefreshToken')
            ->with("12HHIIK")
            ->will($this->returnValue($responseToken));

        $user = $this->getExpectedMockedUser(false);

        $this->assertEquals($user, $this->userProvider->refreshUser($user));
        $this->assertTrue($user->isCredentialsNonExpired());
    }

    private function getResponseToken()
    {
        return array(
                    "id" => 2,
                    "access_token" => "321IUKKL",
                    "expires_in" => 3600,
                    "token_type" => "password",
                    "roles" => "role_1",
                    "refresh_token" => "12HHIIK",
                    'enabled' => true,
                    'username' => 'username',
                );
    }

    private function getExpectedUser($isCredentialsNonExpired = true)
    {
        return new User(2, 'username', '321IUKKL', '12HHIIK', true, 'password', 3600, array('role_1'));
    }

    private function getExpectedMockedUser($isCredentialsNonExpired = true)
    {
        $now = time();
        TimeHelper::$time = $now;
        $user = new User(2, 'username', '321IUKKL', '12HHIIK', true, 'password', 3600, array('role_1'));

        if($isCredentialsNonExpired){
            TimeHelper::$time = $now + 1000;
        }else{
            TimeHelper::$time = $now + 3700;
        }
        
        return $user;
    }

    private function getUsernameNotFoundException()
    {
        return $this->getMockBuilder('Symfony\Component\Security\Core\Exception\UsernameNotFoundException')
            ->disableOriginalConstructor()
            ->getMock();
    }


    private function getApiException()
    {
        return $this->getMockBuilder('Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\Exception\ApiException')
            ->disableOriginalConstructor()
            ->getMock();
    }
}

if(!function_exists('Ant\Bundle\ChateaSecureBundle\Security\User\time')) {
    function time()
    {
        if(TimeHelper::$time != null){
            return TimeHelper::$time;
        }else{
            return \time();
        }
    }
}

class TimeHelper
{
    public static $time = null;
}