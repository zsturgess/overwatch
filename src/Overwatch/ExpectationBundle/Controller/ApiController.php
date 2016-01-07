<?php

namespace Overwatch\ExpectationBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ApiController
 * Handles API requests made for expectations
 *
 * @Route("/api/expectations")
 */
class ApiController extends Controller
{
    /**
     * Returns a list of all the expectations that are installed and available to use in tests
     *
     * @Route("")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     * @ApiDoc(
     *     resource=true,
     *     tags={
     *         "Super Admin" = "#ff1919",
     *         "Admin" = "#ffff33"
     *     }
     * )
     */
    public function getAllAction()
    {
        return new JsonResponse(
            $this->get('overwatch_expectation.expectation_manager')->getAll()
        );
    }
}
