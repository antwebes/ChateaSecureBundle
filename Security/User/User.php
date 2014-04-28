<?php
namespace Ant\Bundle\ChateaSecureBundle\Security\User;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class User implements AdvancedUserInterface
{
    private $id;
    private $username;
    private $accessToken;
    private $refreshToken;
    private $validated;
    private $enabled;
    private $tokenType;
    private $expired_at;
    private $accountNonLocked;
    private $scopes;
    private $roles;

    /**
     * 
     * @param int $id
     * @param string $username
     * @param unknown $accessToken
     * @param unknown $refreshToken
     * @param bool $validated value enabled in API, this value is the value really if user is enabled or disabled 
     * @param string $tokenType
     * @param number $expiresIn
     * @param array $scopes for example password
     * @param array $roles
     */
    public function __construct($id, $username, $accessToken, $refreshToken, $validated, $tokenType = 'Bearer', $expiresIn = 0, array $scopes=array(), array $roles = array() )
    {
        $this->id = $id;
        $this->username = $username;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->validated = $validated;
        $this->tokenType = $tokenType;


        $this->setExpiresIn($expiresIn);

        $this->accountNonLocked = true;
        $this->enabled = true;

        $this->roles = $roles;
        $this->scopes = $scopes;
        /* @deprecated 10-03-2014
        array_push($this->roles, 'ROLE_API_USER');
        */
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }
    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }
    public function getTokenType()
    {
        return $this->tokenType;
    }
    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getExpiresAt()
    {
        return $this->expired_at;
    }

    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return Boolean true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return Boolean true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }
    /**
     * Checks whether the user's credentials (token) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return Boolean true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return $this->expired_at > time() ;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return Boolean true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    /**
     * Checks whether the user validated its mail.
     *
     * @return Boolean true if the user is validated, false otherwise
     *
     * @see DisabledException
     */
    public function isValidated()
    {
    	return $this->validated;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * Sets the access token
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Sets the number of seconds from now when the access token expires
     * @param int expiresIn
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expired_at = $expiresIn > 0 ? time() + $expiresIn : 0;
    }

    /**
     * Sets the refresh token
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }
}