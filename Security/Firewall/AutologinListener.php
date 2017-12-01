<?php

namespace Ant\Bundle\ChateaSecureBundle\Security\Firewall;

use Ant\Bundle\ChateaSecureBundle\Security\Token\AccessTokenToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AutologinListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;
    protected $authorizationChecker;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * This interface must be implemented by firewall listeners.
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if($request->query->has('autologin')){
            $token = new AccessTokenToken($request->query->get('autologin'));

            try{
                $this->authenticateIfUserIsNotLoggedIn($token);
                $this->setRedirectResponse($event);
            }catch(\Exception $failed) {
                $this->setRedirectResponse($event);
            }
        }
    }

    /**
     * @param GetResponseEvent $event
     * @param $request
     */
    private function setRedirectResponse(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $request->query->remove('autologin');
        $request->overrideGlobals();

        $redirectResponse = new RedirectResponse($request->getUri());
        $event->setResponse($redirectResponse);
    }

    /**
     * @param $token
     */
    private function authenticateIfUserIsNotLoggedIn($token)
    {
        if($this->tokenIsAllreadyLoggedIn($token)){
            return;
        }

        $authToken = $this->authenticationManager->authenticate($token);

        $this->tokenStorage->setToken($authToken);
    }

    /**
     * Verifies if the logged in user has the same token
     * @return boolean
     */
    private function tokenIsAllreadyLoggedIn($token)
    {
        return $this->tokenStorage->getToken() !== null &&
            $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') &&
            $this->veryfyAccessTokenIsEqualToLoggedInUsersAccessToken($token);
    }

    private function veryfyAccessTokenIsEqualToLoggedInUsersAccessToken($token)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $user->getAccessToken() == $token->getAccessToken();
    }
}