<?php

namespace Ant\Bundle\ChateaSecureBundle\Security\Token;


use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AccessTokenToken extends UsernamePasswordToken
{
    public function __construct($accessToken, $roles = array())
    {
        $this->accessToken = $accessToken;
        parent::__construct('', $accessToken, 'access-token', $roles);
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}