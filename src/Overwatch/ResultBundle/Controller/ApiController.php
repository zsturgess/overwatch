<?php

namespace Overwatch\ResultBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
class ApiController extends Controller
{
    /**
     * Returns the latest results from across all tests
     *
     * @Route("")
     * @Method({"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ApiDoc(
     *     resource=true,
     *     filters={
     *         {"name"="pageSize", "description"="How many results to return per page","type"="Integer","default"=10,"maximum"=100},
     *         {"name"="page", "description"="The page number to return results from","type"="Integer","default"=1}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919"
     *     }
     * )
     */
    public function getResultsAction(Request $request)
    {
        $size = $request->query->get('pageSize', 10);

        $results = $this->getEntityRepository('OverwatchResultBundle:TestResult')->getResults(
            [],
            ($size >= 100) ? 10 : $size,
            $request->query->get('page', 1)
        );

        return new JsonResponse($results);
    }

    /**
     * Returns the latest results for each test in the requested group
     *
     * @Route("/group/{id}")
     * @Method({"GET"})
     * @Security("is_granted('view', group)")
     * @ApiDoc(
     *     requirements={
     *         {"name"="id", "description"="The ID of the group for which to return results for", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     filters={
     *         {"name"="pageSize", "description"="How many results to return per test per page","type"="Integer","default"=10,"maximum"=100},
     *         {"name"="page", "description"="The page number to return results from","type"="Integer","default"=1}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     */
    public function getRecentGroupResultsAction(TestGroup $group, Request $request)
    {
        $results = [];
        $size = $request->query->get('pageSize', 10);

        foreach ($group->getTests()->toArray() as $test) {
            $results[] = $this->getEntityRepository('OverwatchResultBundle:TestResult')->getResults(
                [
                    'test' => $test
                ],
                ($size >= 100) ? 10 : $size,
                $request->query->get('page', 1)
            );
        }

        return new JsonResponse($results);
    }

    /**
     * Returns the latest results for the given test
     *
     * @Route("/test/{id}")
     * @Method({"GET"})
     * @ApiDoc(
     *     filters={
     *         {"name"="pageSize", "description"="How many results to return per page","type"="Integer","default"=10,"maximum"=100},
     *         {"name"="page", "description"="The page number to return results from","type"="Integer","default"=1}
     *     },
     *     requirements={
     *         {"name"="id", "description"="The ID of the test for which to return results for", "dataType"="integer", "requirement"="\d+"}
     *     },
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33",
     *         "User" = "#75ff47"
     *     }
     * )
     */
    public function getResultsForTestAction(Request $request, Test $test)
    {
        if (!$this->isGranted(TestGroupVoter::VIEW, $test->getGroup())) {
            throw new AccessDeniedHttpException('You must be a member of this test\'s group to see it\'s results');
        }

        $size = $request->query->get('pageSize', 10);

        $results = $this->getEntityRepository('OverwatchResultBundle:TestResult')->getResults(
            [
                'test' => $test
            ],
            ($size >= 100) ? 10 : $size,
            $request->query->get('page', 1)
        );

        return new JsonResponse($results);
    }

    private function getEntityRepository($entity)
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository($entity);
    }
}
