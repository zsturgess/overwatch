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
     * @Method({"POST"})
     */
    public function createUser($email) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to locate a user by email address.");
        }
        
        $password = substr(preg_replace("/[^a-zA-Z0-9]/", "", base64_encode(openssl_random_pseudo_bytes(9))),0,8);
        $user = $this->get('fos_user.util.user_manipulator')->create($email, $password, $email, true, false);
        
        //send user e-mail with their pass
        $message = \Swift_Message::newInstance()
        ->setSubject('You have been invited to Overwatch')
        ->setFrom($this->getUser()->getEmail())
        ->setTo($email)
        ->setBody(
            $this->renderView(
                'OverwatchUserBundle:Email:invited.txt.twig',
                [
                    'inviter' => $this->getUser()->getEmail(),
                    'email' => $email,
                    'password' => $password
                ]
            )
        );
        $this->get('mailer')->send($message);
        
        return new JsonResponse($user, JsonResponse::HTTP_CREATED);
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
    
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAllUsers() {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to locate a user by email address.");
        }
        
        $users = $this->_em->getRepository("OverwatchUserBundle:User")->findAll();
        return new JsonResponse($users);
    }
    
    /**
     * @Route("/{id}/lock")
     * @Method({"PUT","POST"})
     */
    public function toggleLockUser(User $user) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to locate a user by email address.");
        }
        
        if ($user->getId() === $this->getUser()->getId()) {
            throw new AccessDeniedHttpException("You may not toggle locks on yourself.");
        }
        
        $user->setLocked(!$user->isLocked());
        $this->_em->flush();
        
        return new JsonResponse($user);
    }
    
    /**
     * @Route("/{id}/role/{role}")
     * @Method({"PUT","POST"})
     */
    public function setUserRole(User $user, $role) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to locate a user by email address.");
        }
        
        if ($user->getId() === $this->getUser()->getId()) {
            throw new AccessDeniedHttpException("You may not set roles on yourself.");
        }
        
        if (in_array($role, ["ROLE_USER", "ROLE_ADMIN", "ROLE_SUPER_ADMIN"])) {
            $user->setRoles([$role]);
        }
        
        $this->_em->flush();
        
        return new JsonResponse($user);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function deleteUser(User $user) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to locate a user by email address.");
        }
        
        if ($user->getId() === $this->getUser()->getId()) {
            throw new AccessDeniedHttpException("You may not delete yourself.");
        }
        
        $this->_em->remove($user);
        $this->_em->flush();
        
        return new JsonResponse(NULL, JsonResponse::HTTP_NO_CONTENT);
    }
}
