<?php

namespace Overwatch\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AppController
 * Renders the initial view to pass frontend off to AngularJS
 */
class AppController extends Controller
{
    private $_em;
    
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->_em = $this->getDoctrine()->getManager();
    }
    
    /**
     * @Route("")
     * @Template("::base_angular.html.twig")
     */
    public function indexAction()
    {
        return [];
    }
    
    /**
     * @Route("/profile/reset-api-key")
     */
    public function resetApiKeyAction()
    {
        $this->getUser()->resetApiKey();
        $this->_em->flush();
        
        return $this->redirect($this->generateUrl('overwatch_user_app_index') . '#/my-account');
    }
}
