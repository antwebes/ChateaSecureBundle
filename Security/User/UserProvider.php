<?php
namespace Ant\Bundle\ChateaSecureBundle\Security\User;

use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\AuthenticationException;
use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\Exception\ApiException;
use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\HttpAdapterInterface;
use Ant\Bundle\ChateaClientBundle\Api\Model\User as ApiUser;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UserProvider implements ChateaUserProviderInterface
{
    private $authentication;
    private $translator;

    /**
     * @param HttpAdapterInterface $authentication
     * @param TranslatorInterface $translator
     */
    public function __construct(HttpAdapterInterface $authentication, TranslatorInterface $translator)
    {
        $this->authentication = $authentication;
        $this->translator = $translator;
    }

    /**
     * Loads a user from the API if the username and password are valid
     * @param $username
     * @param $password
     * @return User
     */
    public function loadUser($username, $password)
    {
        if (empty($username)) {
            //here now I throw UsernameNotFoundException, because this exception are catched in UserAuthenticationProvider https://github.com/symfony/security-core/blob/master/Authentication/Provider/UserAuthenticationProvider.php#L71
            //and or throw this exception to show the error or override UserAuthenticationProvider of Symfony
            throw new UsernameNotFoundException($this->translator->trans('login.username_not_empty', array(), 'Login'));
        }

        if(empty($password)) {
            //here now I throw UsernameNotFoundException, because this exception are catched in UserAuthenticationProvider https://github.com/symfony/security-core/blob/master/Authentication/Provider/UserAuthenticationProvider.php#L71
            //and or throw this exception to show the error or override UserAuthenticationProvider of Symfony
            throw new UsernameNotFoundException($this->translator->trans('login.password_not_empty', array(), 'Login'));
        }

        try {
            $data = $this->authentication->withUserCredentials($username, $password);
            return $this->mapJsonToUser($data);
        } catch (ApiException $ae) {

            throw new BadCredentialsException($this->translator->trans('login.service_down', array(), 'Login'));
        } catch (AuthenticationException $e) {
            $error = 'login.incorrect_credentialas';
            try {
                $jsonResponse = json_decode($e->getMessage(), true);

                if(isset($jsonResponse['error']) && $jsonResponse['error'] == 'user_disabled'){
                    $error = 'login.user_disabled';
                }
            }catch(\Exception $e){

            }

            if($error == 'login.user_disabled'){
                throw new BadCredentialsException($this->translator->trans($error, array('%username%' => $username), 'Login'),30,$e);
            }else{
                throw new UsernameNotFoundException($this->translator->trans($error, array('%username%' => $username), 'Login'),30,$e);
            }

        }
    }

    /**
     * Authenticate with facebook id
     * @param $facebookId
     * @return User
     */
    public function loadUserByFacebookId($facebookId)
    {
        if (empty($facebookId)) {
            throw new \InvalidArgumentException($this->translator->trans('login.facebookid_not_empty', array(), 'Login'));
        }

        try {
            $data = $this->authentication->withFacebookId($facebookId);
            return $this->mapJsonToUser($data);
        } catch (ApiException $ae) {
            throw new BadCredentialsException($this->translator->trans('login.service_down', array(), 'Login'));
        } catch (AuthenticationException $e) {
            throw new UsernameNotFoundException($this->translator->trans('login.incorrect_facebookid', array(), 'Login'),30,$e);
        }
    }

    /**
     * Authenticate with access token
     * @param $accessToken
     * @return User
     */
    public function loadUserByAccessToken($accessToken)
    {
        if (empty($accessToken)) {
            throw new \InvalidArgumentException($this->translator->trans('login.accesstoken_not_empty', array(), 'Login'));
        }

        try {
            $data = $this->authentication->withAccessToken($accessToken);
            return $this->mapJsonToUser($data);
        } catch (ApiException $ae) {
            throw new BadCredentialsException($this->translator->trans('login.service_down', array(), 'Login'));
        } catch (AuthenticationException $e) {
            throw new UsernameNotFoundException($this->translator->trans('login.incorrect_access_token', array(), 'Login'),30,$e);
        }
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws \Exception if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        throw new \Exception($this->translator->trans('login.method_not_supported', array(), 'Login'));
    }


    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     *
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if($user instanceof ApiUser){ //if I have an ApiUser instance I will try to obtain data from the server
            return $this->loadUser($user->getUsername(), $user->getPlainPassword());
        }else if (!$user instanceof User){
            $ex = new UnsupportedUserException($this->translator->trans('login.class_not_supported', array('%class%' => get_class($user))));
            
            throw $ex;
        }

        /*
         * if the user has an expired access token we try to auntenticate with the refresh token
         * In casde we don't success with the autentication we throw an exception
         */
        if(!$user->isCredentialsNonExpired()){
            $refreshToken = $user->getRefreshToken();
            
            if(empty($refreshToken)){
                throw new UsernameNotFoundException($this->translator->trans('login.incorrect_credentialas', array('%username%' => $user->getUsername())),30,null);
            }

            try {
                $data = $this->authentication->withRefreshToken($refreshToken);

                //onevr we have authenticated wee add the acces token, refesh token and the expires in
                $user->setAccessToken($data['access_token']);
                $user->setRefreshToken($data['refresh_token']);
                $user->setExpiresIn($data['expires_in']);
            } catch (AuthenticationException $e) {
                throw new UsernameNotFoundException($this->translator->trans('login.incorrect_credentialas', array('%username%' => $user->getUsername())),30,$e);
            }
        }

        return $user;
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function supportsClass($class)
    {
        return $class === 'Ant\Bundle\ChateaSecureBundle\Security\User\User';
    }

    /**
     * maps the returned data from the server to a user
     * @param $data
     * @return User
     */
    protected function mapJsonToUser($data)
    {
        return new User(
            $data['id'],
            $data['username'],
            $data['access_token'],
            $data['refresh_token'],
        	$data['enabled'],
            $data['token_type'],
            $data['expires_in'],
        	explode(',', $data['scope']),
			$data['roles']
        );
    }
}