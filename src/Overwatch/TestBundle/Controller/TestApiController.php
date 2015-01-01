<?php

namespace Overwatch\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Overwatch\ExpectationBundle\Exception\ExpectationNotFoundException;
use Overwatch\TestBundle\Entity\Test;
use Overwatch\TestBundle\Entity\TestGroup;
use Overwatch\TestBundle\Security\TestGroupVoter;

/**
 * ApiController
 * Handles API requests made for Tests
 * 
 * @Route("/api/tests")
 */
class TestApiController extends Controller {
    private $_em;
    private $expectationManager;
    
    public function setContainer(ContainerInterface $container = NULL) {
        parent::setContainer($container);
        $this->_em = $this->getDoctrine()->getManager();
        $this->expectationManager = $this->get("overwatch_expectation.expectation_manager");
    }

    /**
     * @Route("/group/{id}")
     * @Method({"POST"})
     */
    public function createTest(Request $request, TestGroup $group) {
        if (!$this->isGranted(TestGroupVoter::EDIT, $group)) {
            throw new AccessDeniedHttpException("You must be an admin of this group to create a test in it.");
        }
        
        $autoName = "Expect " . $request->get('actual') . " " . $request->get('expectation') . " " . $request->get('expected');
        
        $test = new Test();
        $test
            ->setActual($request->get('actual'))
            ->setExpectation($request->get('expectation'))
            ->setExpected($request->get('expected'))
            ->setName($request->get('name'), $autoName)
            ->setGroup($group)
        ;
        
        try {
            $this->expectationManager->get($test->getExpectation());
        } catch (ExpectationNotFoundException $ex) {
            return new JsonResponse("Expectation '" . $test->getExpectation() . "' could not be found", JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        if ($test->getActual() === NULL) {
            return new JsonResponse("An actual value to test against must be provided.", JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $this->_em->persist($test);
        $this->_em->flush();
        
        return new JsonResponse($test, JsonResponse::HTTP_CREATED);
    }
    
    /**
     * @Route("/group/{id}")
     * @Method({"GET"})
     */
    public function getTestsInGroup(TestGroup $group) {
        if (!$this->isGranted(TestGroupVoter::VIEW, $group)) {
            throw new AccessDeniedHttpException("You must be a member of this group to view tests in it");
        }
        
        return new JsonResponse($group->getTests());
    }
    
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function getTest(Test $test) {
        if (!$this->isGranted(TestGroupVoter::VIEW, $test->getGroup())) {
            throw new AccessDeniedHttpException("You must be a member of this test's group to view it");
        }
        
        return new JsonResponse($test);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function updateTest(Request $request, Test $test) {
        if (!$this->isGranted(TestGroupVoter::EDIT, $test->getGroup())) {
            throw new AccessDeniedHttpException("You must be an admin in this test's group to edit it");
        }
        
        foreach (['name', 'actual', 'expectation', 'expected'] as $field) {
            if ($request->request->has($field)) {
                $test->{"set".ucfirst($field)}($request->request->get($field));
            }
        }
        
        $this->_em->flush();
        return new JsonResponse($test);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function deleteTest(Test $test) {
        if (!$this->isGranted(TestGroupVoter::EDIT, $test->getGroup())) {
            throw new AccessDeniedHttpException("You must be an admin in this test's group to delete it");
        }
        
        $this->_em->remove($test);
        $this->_em->flush();
        
        return new JsonResponse(NULL, JsonResponse::HTTP_NO_CONTENT);
    }
}
