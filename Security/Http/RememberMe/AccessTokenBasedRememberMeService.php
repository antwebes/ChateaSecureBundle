<?php
namespace Ant\Bundle\ChateaSecureBundle\Security\Http\RememberMe;

use Ant\Bundle\ChateaSecureBundle\Security\User\User;
use Symfony\Component\Security\Http\RememberMe\AbstractRememberMeServices;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessTokenBasedRememberMeService extends AbstractRememberMeServices
{
    protected function processAutoLoginCookie(array $cookieParts, Request $request)
    {
        if(count($cookieParts) !== 6) {
            ldd($cookieParts);
            throw new AuthenticationException('The cookie is invalid.');
        }

        list($id, $username, $accessToken, $expiresAt, $enabled, $roles) = $cookieParts;

        $authUserData = array(
                'id' => $id,
                'access_token' => $accessToken,
                'refresh_token' => '',
                'token_type' => '',
                'enabled' => $enabled,
                'expires_in' => $expiresAt - time(),
                'scope' => '',
                'roles' => $roles
            );

        $user = $this->mapJsonToUser($authUserData, $username);

        if(!$user->isCredentialsNonExpired()){
            throw new AuthenticationException('The cookie is invalid.');    
        }

        return $user;
    }

    public function regenerateRememberMeTokenIfPresent(Request $request, Response $response, TokenInterface $token)
    {
        if (!$this->hasRememberMeCookie($request)) {
            return;
        }

        $this->onLoginSuccess($request, $response, $token);
    }

    /**
     * {@inheritDoc}
     */
    protected function onLoginSuccess(Request $request, Response $response, TokenInterface $token)
    {
        $user = $token->getUser();
        $expires = time() + $this->options['lifetime'];
        
        $value = $this->generateCookieValue(
            $user->getId(),
            $user->getUsername(),
            $user->getAccessToken(),
            $user->getExpiresAt(),
            $user->isValidated(),
            $user->getRoles()
            );

        $response->headers->setCookie(
            new Cookie(
                $this->options['name'],
                $value,
                $expires,
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            )
        );
    }

    protected function generateCookieValue($id, $username, $accessToken, $expiresAt, $enabled, $roles)
    {
        return $this->encodeCookie(array(
            $id,
            $username,
            $accessToken,
            $expiresAt,
            $enabled,
            $roles
        ));
    }

    protected function mapJsonToUser($data, $username)
    {
        return new User(
            $data['id'],
            $username,
            $data['access_token'],
            $data['refresh_token'],
            $data['enabled'],
            $data['token_type'],
            $data['expires_in'],
            explode(',', $data['scope']),
            $data['roles']
        );
    }

    private function hasRememberMeCookie(Request $request)
    {
        $cookieName = $this->options['name'];
        return $request->cookies->get($cookieName) !== null;
    }

    protected function decodeCookie($rawCookie)
    {
        $cookie = parent::decodeCookie($rawCookie);

        $fromJson = function($element){
            $arr = json_decode($element, true);

            return $arr;
        };

        return array_map($fromJson, $cookie);
    }

    protected function encodeCookie(array $cookieParts)
    {
        $serializeToJson = function($element){
            return json_encode($element);
        };

        return parent::encodeCookie(array_map($serializeToJson, $cookieParts));
    }
}