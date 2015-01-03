<?php

namespace Overwatch\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Overwatch\TestBundle\Entity\TestGroup;
use Overwatch\TestBundle\Security\TestGroupVoter;
use Overwatch\UserBundle\Entity\User;

/**
 * TestGroupApiController
 * Handles API requests made for TestGroups
 * 
 * @Route("/api/groups")
 */
class TestGroupApiController extends Controller {
    private $_em;
    
    public function setContainer(ContainerInterface $container = NULL) {
        parent::setContainer($container);
        $this->_em = $this->getDoctrine()->getManager();
    }
    
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function createGroup(Request $request) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to create a group");
        }
        
        $content = $request->getContent();
        if (empty($content)) {
            return new JsonResponse("You must pass a name for the new group", 422);
        }
        $params = json_decode($content, true);
        
        $group = new TestGroup;
        $group
            ->setName($params["name"])
        ;
        
        $this->_em->persist($group);
        $this->_em->flush();
        
        return new JsonResponse($group, JsonResponse::HTTP_CREATED);
    }
    
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAllGroups() {
        if ($this->isGranted("ROLE_SUPER_ADMIN")) {
            $groups = $this->_em->getRepository("OverwatchTestBundle:TestGroup")->findAll();
        } else if ($this->getUser() !== NULL) {
            $groups = $this->getUser()->getGroups()->toArray();
        } else {
            throw new AccessDeniedHttpException("Please login");
        }
        
        return new JsonResponse($groups);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function getGroup(TestGroup $group) {
        if (!$this->isGranted(TestGroupVoter::VIEW, $group)) {
            throw new AccessDeniedHttpException("You must be a member of this group to view it");
        }
        
        return new JsonResponse($group);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function updateGroup(Request $request, TestGroup $group) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to update this group");
        }
        
        $content = $request->getContent();
        if (empty($content)) {
            return new JsonResponse("You must pass a new group name", 422);
        }
        $params = json_decode($content, true);
        
        if ($params["name"]) {
            $group->setName($params["name"]);
            
            $this->_em->flush();
        }
        
        return new JsonResponse($group);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function deleteGroup(Request $request, TestGroup $group) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to delete this group");
        }
        
        if ($group->getUsers()->count() + $group->getTests()->count() !== 0) {
            return new JsonResponse("This group still has users and/or tests in it. You must remove them before continuing.", JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $this->_em->remove($group);
        $this->_em->flush();
        
        return new JsonResponse(NULL, JsonResponse::HTTP_NO_CONTENT);
    }
    
    /**
     * @Route("/{groupId}/user/{userId}")
     * @Method({"POST"})
     * @ParamConverter("group", class="OverwatchTestBundle:TestGroup", options={"id" = "groupId"})
     * @ParamConverter("user", class="OverwatchUserBundle:User", options={"id" = "userId"})
     */
    public function addUserToGroup(TestGroup $group, User $user) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to add users to groups");
        }
        
        $group->addUser($user);
        $this->_em->flush();
        
        return new JsonResponse($user, JsonResponse::HTTP_CREATED);
    }
    
    /**
     * @Route("/{groupId}/user/{userId}")
     * @Method({"DELETE"})
     * @ParamConverter("group", class="OverwatchTestBundle:TestGroup", options={"id" = "groupId"})
     * @ParamConverter("user", class="OverwatchUserBundle:User", options={"id" = "userId"})
     */
    public function removeUserFromGroup(TestGroup $group, User $user) {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            throw new AccessDeniedHttpException("You must be a super admin to remove users from groups");
        }
        
        $group->removeUser($user);
        $this->_em->flush();
        
        return new JsonResponse($user);
    }
}
