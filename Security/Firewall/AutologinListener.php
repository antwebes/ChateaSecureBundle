<?php

namespace Ant\Bundle\ChateaSecureBundle\Security\Firewall;

use Ant\Bundle\ChateaSecureBundle\Security\Token\AccessTokenToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class AutologinListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    /**
     * @param SecurityContextInterface $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
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
        if($this->securityContext->getToken() !== null && $this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')){
            return;
        }

        $authToken = $this->authenticationManager->authenticate($token);

        $this->securityContext->setToken($authToken);
    }
}