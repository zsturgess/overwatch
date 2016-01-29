<?php

namespace Overwatch\TestBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Overwatch\ExpectationBundle\Exception\ExpectationNotFoundException;
use Overwatch\TestBundle\Entity\Test;
use Overwatch\TestBundle\Entity\TestGroup;
use Overwatch\TestBundle\Security\TestGroupVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * ApiController
 * Handles API requests made for Tests
 * 
 * @Route("/api/tests")
 */
class TestApiController extends Controller
{
    private $_em;
    private $expectationManager;
    
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->_em = $this->getDoctrine()->getManager();
        $this->expectationManager = $this->get('overwatch_expectation.expectation_manager');
    }

    /**
     * Returns the details of the given test
     * 
     * @Route("/{id}")
     * @Method({"GET"})
     * @ApiDoc(
     *     resource=true,
     *     requirements={
     *         {"name"="id", "description"="The ID of the test to return", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     */
    public function getTestAction(Test $test)
    {
        if (!$this->isGranted(TestGroupVoter::VIEW, $test->getGroup())) {
            throw $this->createAccessDeniedException('You must be a member of this test\'s group to view it');
        }
        
        return new JsonResponse($test);
    }
    
    /**
     * Creates a test in the given group
     * 
     * @Route("/group/{id}")
     * @Method({"POST"})
     * @Security("is_granted('edit', group)")
     * @ApiDoc(
     *     resource=true,
     *     parameters={
     *         {"name"="name", "description"="A user-friendly name for the test", "required"=true, "format"="Github Status", "dataType"="string"},
     *         {"name"="actual", "description"="The actual value to test against", "required"=true, "format"="status.github.com", "dataType"="string"},
     *         {"name"="expectation", "description"="The expectation to test with", "required"=true, "format"="toResolveTo", "dataType"="string"},
     *         {"name"="expected", "description"="The expected value to test against", "required"=false, "format"="octostatus-production.github.com", "dataType"="string"},
     *     },
     *     requirements={
     *         {"name"="id", "description"="The ID of the group to create the test under", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33"
     *     }
     * )
     */
    public function createTestAction(Request $request, TestGroup $group)
    {
        $test = new Test();
        $test
            ->setActual($request->request->get('actual'))
            ->setExpectation($request->request->get('expectation'))
            ->setExpected($request->request->get('expected'))
            ->setName($request->request->get('name'))
            ->setGroup($group)
        ;
        
        try {
            $this->expectationManager->get($test->getExpectation());
        } catch (ExpectationNotFoundException $ex) {
            return new JsonResponse("Expectation '" . $test->getExpectation() . "' could not be found", JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        if ($test->getActual() === null) {
            return new JsonResponse('An actual value to test against must be provided.', JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $this->_em->persist($test);
        $this->_em->flush();
        
        return new JsonResponse($test, JsonResponse::HTTP_CREATED);
    }
    
    /**
     * Returns a list of tests in the given group
     * 
     * @Route("/group/{id}")
     * @Method({"GET"})
     * @Security("is_granted('view', group)")
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the group to return tests from", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     */
    public function getTestsInGroupAction(TestGroup $group)
    {
        return new JsonResponse($group->getTests()->toArray());
    }
    
    /**
     * Updates the details of the given test
     * 
     * @Route("/{id}")
     * @Method({"PUT"})
     * @ApiDoc(
     *     parameters={
     *         {"name"="name", "description"="A user-friendly name for the test", "required"=false, "format"="Github Status", "dataType"="string"},
     *         {"name"="actual", "description"="The actual value to test against", "required"=false, "format"="status.github.com", "dataType"="string"},
     *         {"name"="expectation", "description"="The expectation to test with", "required"=false, "format"="toResolveTo", "dataType"="string"},
     *         {"name"="expected", "description"="The expected value to test against", "required"=false, "format"="octostatus-production.github.com", "dataType"="string"},
     *     },
     *     requirements={
     *         {"name"="id", "description"="The ID of the test to edit the details of", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33"
     *     }
     * )
     */
    public function updateTestAction(Request $request, Test $test)
    {
        if (!$this->isGranted(TestGroupVoter::EDIT, $test->getGroup())) {
            throw $this->createAccessDeniedException('You must be an admin in this test\'s group to edit it');
        }
        
        foreach (['name', 'actual', 'expectation', 'expected'] as $field) {
            if ($request->request->has($field)) {
                $test->{'set' . ucfirst($field)}($request->request->get($field));
            }
        }
        
        $this->_em->flush();
        return new JsonResponse($test);
    }
    
    /**
     * Deletes the given test
     * 
     * @Route("/{id}")
     * @Method({"DELETE"})
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the test to delete", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33"
     *     }
     * )
     */
    public function deleteTestAction(Test $test)
    {
        if (!$this->isGranted(TestGroupVoter::EDIT, $test->getGroup())) {
            throw $this->createAccessDeniedException('You must be an admin in this test\'s group to delete it');
        }
        
        $this->_em->remove($test);
        $this->_em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
    
    /**
     * Runs a test
     * 
     * @Route("/{id}")
     * @Method({"POST"})
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the test to run", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33"
     *     }
     * )
     */
    public function runTestAction(Test $test)
    {
        if (!$this->isGranted(TestGroupVoter::EDIT, $test->getGroup())) {
            throw $this->createAccessDeniedException('You must be an admin in this test\'s group to run it');
        }
        
        $result = $this->expectationManager->run($test);
        
        $this->_em->persist($result);
        $this->_em->flush();
        
        return new JsonResponse($result, JsonResponse::HTTP_CREATED);
    }
}
