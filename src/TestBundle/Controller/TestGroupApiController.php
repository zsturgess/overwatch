<?php

namespace Overwatch\TestBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Overwatch\TestBundle\Entity\TestGroup;
use Overwatch\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * TestGroupApiController
 * Handles API requests made for TestGroups
 * 
 * @Route("/api/groups")
 */
class TestGroupApiController extends Controller
{
    private $_em;
    
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->_em = $this->getDoctrine()->getManager();
    }
    
    /**
     * Creates a new group
     * 
     * @Route("")
     * @Method({"POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     resource=true,
     *     parameters={
     *         {"name"="name", "description"="A user-friendly name for the group", "required"=true, "format"="Group 1", "dataType"="string"},
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function createGroupAction(Request $request)
    {
        if ($request->request->get('name') === null) {
            return new JsonResponse('You must provide a name for the new group', JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $group = new TestGroup;
        $group
            ->setName($request->request->get('name'))
        ;
        
        $this->_em->persist($group);
        $this->_em->flush();
        
        return new JsonResponse($group, JsonResponse::HTTP_CREATED);
    }
    
    /**
     * Returns a list of all groups the current user has access to
     * 
     * @Route("")
     * @Method({"GET"})
     * @Security("has_role('ROLE_USER')")
     * @ApiDoc(
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     */
    public function getAllGroupsAction()
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $groups = $this->_em->getRepository('OverwatchTestBundle:TestGroup')->findAll();
        } else {
            $groups = $this->getUser()->getGroups()->toArray();
        }
        
        return new JsonResponse($groups);
    }
    
    /**
     * Returns the details of the specified group
     * 
     * @Route("/{id}")
     * @Method({"GET"})
     * @Security("is_granted('view', group)")
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the group to return", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     */
    public function getGroupAction(TestGroup $group)
    {
        return new JsonResponse($group);
    }
    
    /**
     * Updates the given group
     * 
     * @Route("/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     parameters={
     *         {"name"="name", "description"="A user-friendly name for the group", "required"=false, "format"="Group 1", "dataType"="string"}
     *     },
     *     requirements={
     *         {"name"="id", "description"="The ID of the group to edit", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * ) 
     */
    public function updateGroupAction(Request $request, TestGroup $group)
    {
        if ($request->request->has('name')) {
            $group->setName($request->request->get('name'));
            
            $this->_em->flush();
        }
        
        return new JsonResponse($group);
    }
    
    /**
     * Deletes the given group
     * 
     * @Route("/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the group to delete", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function deleteGroupAction(TestGroup $group)
    {
        if ($group->getUsers()->count() + $group->getTests()->count() !== 0) {
            return new JsonResponse('This group still has users and/or tests in it. You must remove them before continuing.', JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $this->_em->remove($group);
        $this->_em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
    
    /**
     * Adds the given user to the given group
     * 
     * @Route("/{groupId}/user/{userId}")
     * @Method({"POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("group", class="OverwatchTestBundle:TestGroup", options={"id" = "groupId"})
     * @ParamConverter("user", class="OverwatchUserBundle:User", options={"id" = "userId"})
     * @ApiDoc(
     *     resource=true,
     *     requirements={
     *         {"name"="userId", "description"="The ID of the user", "dataType"="integer", "requirement"="\d+"},
     *         {"name"="groupId", "description"="The ID of the group", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function addUserToGroupAction(TestGroup $group, User $user)
    {
        $group->addUser($user);
        $this->_em->flush();
        
        return new JsonResponse($user, JsonResponse::HTTP_CREATED);
    }
    
    /**
     * Removes the given user from the given group
     * 
     * @Route("/{groupId}/user/{userId}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("group", class="OverwatchTestBundle:TestGroup", options={"id" = "groupId"})
     * @ParamConverter("user", class="OverwatchUserBundle:User", options={"id" = "userId"})
     * @ApiDoc(
     *     requirements={
     *         {"name"="userId", "description"="The ID of the user", "dataType"="integer", "requirement"="\d+"},
     *         {"name"="groupId", "description"="The ID of the group", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function removeUserFromGroupAction(TestGroup $group, User $user)
    {
        $group->removeUser($user);
        $this->_em->flush();
        
        return new JsonResponse($user);
    }
}
