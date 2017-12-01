<?php
namespace Ant\Bundle\ChateaSecureBundle\Security\Http\Logout;

use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\HttpAdapterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Ant\Bundle\ChateaSecureBundle\Security\User\User;

class RevokeAccessOnLogoutHandler implements LogoutHandlerInterface
{
    private $tokenStorage;
    private $client;

    function __construct(TokenStorageInterface $tokenStorage, HttpAdapterInterface $client)
    {
        $this->tokenStorage = $tokenStorage;
        $this->client = $client;
    }

    /**
     * This method is called by the LogoutListener when a user has requested
     * to be logged out. Usually, you would unset session variables, or remove
     * cookies, etc.
     *
     * @param Request        $request
     * @param Response       $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $token = $this->tokenStorage->getToken();

        if($this->mustRevokeToken($token)){
            $this->revokeAccessToken($token);
        }
    }

    private function mustRevokeToken($token)
    {
        return $token != null && $token->getUser() instanceof User;
    }

    private function revokeAccessToken($token)
    {
        try {
            $user = $token->getUser();
            //remove this token from the server
            $this->client->revokeToken($user->getAccessToken());
        }catch(\Exception $e){

        }
    }
}