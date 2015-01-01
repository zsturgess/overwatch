<?php

namespace Overwatch\ExpectationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * ApiController
 * Handles API requests made for expectations
 * 
 * @Route("/api/expectations")
 */
class ApiController extends Controller {
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll() {
        if (!$this->isGranted("ROLE_ADMIN")) {
            throw new AccessDeniedHttpException("You must be at least an admin to see all expectations");
        }
        
        return new JsonResponse(
            $this->get("overwatch_expectation.expectation_manager")->getAll()
        );
    }
}
