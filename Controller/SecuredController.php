<?php
namespace Ant\Bundle\ChateaSecureBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SecuredController extends Controller
{
    /**
     * @Route("/login", name="_antwebes_chateaclient_login")
     */
    public function loginAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY') && $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        	$homepage_path = $this->container->getParameter('chatea_secure.homepage_path');
            if ($homepage_path == "/") {
            	return $this->redirect($homepage_path);
            }else{
            	return $this->redirect($this->generateUrl($homepage_path));
            }
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error){
            $error = $this->extractAuthErrorI18N($error);
        }

        return $this->render('ChateaSecureBundle:Secured:login.html.twig',array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }

    /**
     * @Route("/login_check", name="_security_check")
     */
    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/logout", name="_antwebes_chateaclient_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
    }

    private function extractAuthErrorI18N($error)
    {
        $translator = $this->get('translator');
        $translationMap = array(
            'Bad credentials.' => 'login.bad_credentials',
            'Bad credentials' => 'login.bad_credentials'
        );
        $message = $error->getMessage();

        if(isset($translationMap[$message])){
            return $translator->trans($translationMap[$message], array(), 'Login');
        }

        return $message;
    }
}