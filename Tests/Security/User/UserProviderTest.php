<?php
namespace Ant\Bundle\ChateaSecureBundle\Security\User;

use Ant\Bundle\ChateaSecureBundle\Security\User\UserProvider;
use Ant\Bundle\ChateaSecureBundle\Security\User\User;
use Guzzle\Http\Exception\BadResponseException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProviderInternal extends UserProvider
{
    /**
     * @param $data
     * @return User
     */
    public function mapJsonToUser($data)
    {
        return parent::mapJsonToUser($data);
    }
}

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    private $authenticator;
    private $userProvider;
    private $userProviderInternal;
    private $translation;

    public function setUp()
    {
        $this->authenticator = $this->getMockBuilder('Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\HttpAdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translation = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->userProvider = new UserProvider($this->authenticator, $this->translation);
        $this->userProviderInternal = new UserProviderInternal($this->authenticator, $this->translation);
    }

    public function testLoadUserThrowsExceptionIfUsernameIsNotGiven()
    {
        $this->translation
            ->expects($this->once())
            ->method('trans')
            ->with('login.username_not_empty', array(), 'Login')
            ->will($this->returnValue('The username cannot be empty.'));

        try {
            $this->userProvider->loadUser('', 'password');
        } catch (UsernameNotFoundException $e) {
            if($e->getMessage() == 'The username cannot be empty.')
                return;
        }

        $this->fail('Expected to raise an UsernameNotFoundException');
    }

    public function testLoadUserThrowsExceptionIfPasswordIsNotGiven()
    {
        $this->translation
            ->expects($this->once())
            ->method('trans')
            ->with('login.password_not_empty', array(), 'Login')
            ->will($this->returnValue('The password cannot be empty.'));

        try {
            $this->userProvider->loadUser('username', '');
        } catch (UsernameNotFoundException $e) {
            if($e->getMessage() == 'The password cannot be empty.')
                return;
        }

        $this->fail('Expected to raise an UsernameNotFoundException');
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
        $exception = $this->getApiException('Authentication service down');

        $this->authenticator
            ->expects($this->once())
            ->method('withUserCredentials')
            ->with('username', 'password')
            ->will($this->throwException($exception));

        $this->translation
            ->expects($this->once())
            ->method('trans')
            ->with('login.service_down', array(), 'Login')
            ->will($this->returnValue('Authentication service down'));

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

    public function testMapJsonToUser()
    {

        $data = array('id'=>1,
            'username'=>'username-test',
            'access_token'=>'access_token-test',
            'refresh_token'=>'refresh_token-test',
            'enabled'=>true,
            'token_type'=>'token_type-test',
            'expires_in'=>'3600',
            'scope' =>'scope_1,scope_2,scope_3',
            'roles'=>array('ROLE_A','ROLE_B','ROLE_C')
        );

        $user = $this->userProviderInternal->mapJsonToUser($data);
        $this->assertEquals(1,$user->getId());
        $this->assertEquals('username-test',$user->getUsername());
        $this->assertEquals('access_token-test',$user->getAccessToken());
        $this->assertEquals(true,$user->isEnabled());
        $this->assertEquals(array('scope_1','scope_2','scope_3'),$user->getScopes());
        $this->assertEquals(array('ROLE_A','ROLE_B','ROLE_C'),$user->getRoles());

    }

    public function testLoadUserByAccessToken()
    {
        $responseToken = $this->getResponseToken();
        $user = $this->getExpectedUser();

        $this->authenticator
            ->expects($this->once())
            ->method('withAccessToken')
            ->with('anaccesstoken')
            ->will($this->returnValue($responseToken));

        $this->assertEquals($user, $this->userProvider->loadUserByAccessToken('anaccesstoken'));
    }

    private function getResponseToken()
    {
        return array(
                    "id" => 2,
                    "access_token" => "321IUKKL",
                    "expires_in" => 3600,
                    "token_type" => "password",
        			"scope" => "password",
                    "roles" => array("role_1"),
                    "refresh_token" => "12HHIIK",
                    'enabled' => true,
                    'username' => 'username',
                );
    }

    private function getExpectedUser($isCredentialsNonExpired = true)
    {
        return new User(2, 'username', '321IUKKL', '12HHIIK', true, 'password', 3600, array("password"), array('role_1'));
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


    private function getApiException($message = null)
    {
        $exception = $this->getMockBuilder('Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\Exception\ApiException')
            ->disableOriginalConstructor()
            ->getMock();

        if($message !== null){
            $exception->expects($this->any())
                ->method('getMessage')
                ->will($this->returnValue($message));
        }

        return $exception;
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