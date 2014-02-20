<?php
namespace Ant\Bundle\ChateaSecureBundle\Security\User;

use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\AuthenticationException;
use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\Exception\ApiException;
use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\HttpAdapterInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider implements ChateaUserProviderInterface
{
    private $authentication;

    public function __construct(HttpAdapterInterface $authentication)
    {
        $this->authentication = $authentication;
    }

    public function loadUser($username, $password)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        if(empty($password)) {
            throw new \InvalidArgumentException('The password cannot be empty.');
        }
        try {
            $data = $this->authentication->withUserCredentials($username, $password);
            return $this->mapJsonToUser($data);
        } catch (ApiException $ae) {
            throw new BadCredentialsException('Authentication service down');
        } catch (AuthenticationException $e) {
            throw new UsernameNotFoundException(sprintf('Incorrect username or password for %s ', $username),30,$e);
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
        throw new \Exception("this method is not soported");
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
        if (!$user instanceof User){
            $ex = new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
            
            throw $ex;
        }

        if(!$user->isCredentialsNonExpired()){
            $refreshToken = $user->getRefreshToken();
            
            if(empty($refreshToken)){
                throw new UsernameNotFoundException(sprintf('Incorrect username or password for %s ', $user->getUsername()),30,null);
            }

            try {
                $data = $this->authentication->withRefreshToken($refreshToken);
                $user->setAccessToken($data['access_token']);
                $user->setRefreshToken($data['refresh_token']);
                $user->setExpiresIn($data['expires_in']);
            } catch (AuthenticationException $e) {
                throw new UsernameNotFoundException(sprintf('Incorrect username or password for %s ', $user->getUsername()),30,$e);
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
        return $class === ' Ant\Bundle\ChateaSecureBundle\Security\User\User';
    }

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
			explode(',', $data['scope'])
        );
    }
}