<?php

namespace Overwatch\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Overwatch\UserBundle\Entity\User;

/**
 * ApiController
 * Handles API requests made for Users
 * @Route("/api/users")
 */
class ApiController extends Controller {
    private $_em;
    
    public function setContainer(ContainerInterface $container = NULL) {
        parent::setContainer($container);
        $this->_em = $this->getDoctrine()->getManager();
    }
    
    /**
     * @Route("/{email}")
     * @Method({"GET"})
     * @ParamConverter("user", class="OverwatchUserBundle:User")
     */
    public function findUser(User $user) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to locate a user by email address.");
        }
        
        return new JsonResponse($user);
    }
}
