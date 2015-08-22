<?php

namespace Overwatch\ResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Overwatch\TestBundle\Entity\Test;
use Overwatch\TestBundle\Entity\TestGroup;
use Overwatch\TestBundle\Security\TestGroupVoter;

/**
 * ApiController
 * Handles API request made for Result
 * @Route("/api/results")
 */
class ApiController extends Controller {    
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getResults(Request $request) {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedHttpException("You must be a super admin to see all results");
        }
        
        $size = $request->query->get('pageSize', 10);
        
        $results = $this->getEntityRepository("OverwatchResultBundle:TestResult")->getResults(
            [],
            ($size >= 100) ? 10 : $size,
            $request->query->get('page', 1)
        );
        
        return new JsonResponse($results);
    }
    
    /**
     * @Route("/group/{id}")
     * @Method({"GET"})
     */
    public function getRecentGroupResults(TestGroup $group) {
        if (!$this->isGranted(TestGroupVoter::VIEW, $group)) {
            throw new AccessDeniedHttpException("You must be a member of this group to see results for it");
        }
        
        $results = [];
        
        foreach ($group->getTests()->toArray() as $test) {
            $results[] = $this->getEntityRepository("OverwatchResultBundle:TestResult")->getLatest(
                [
                    "test" => $test
                ]
            );
        }
        
        return new JsonResponse($results);
    }
    
    /**
     * @Route("/test/{id}")
     * @Method({"GET"})
     */
    public function getResultsForTest(Request $request, Test $test) {   
        if (!$this->isGranted(TestGroupVoter::VIEW, $test->getGroup())) {
            throw new AccessDeniedHttpException("You must be a member of this test's group to see it's results");
        }
        
        $size = $request->query->get('pageSize', 10);
        
        $results = $this->getEntityRepository("OverwatchResultBundle:TestResult")->getResults(
            [
                "test" => $test
            ],
            ($size >= 100) ? 10 : $size,
            $request->query->get('page', 1)
        );
        
        return new JsonResponse($results);
    }
    
    private function getEntityRepository($entity) {
        return $this->get("doctrine.orm.entity_manager")->getRepository($entity);
    }
}
