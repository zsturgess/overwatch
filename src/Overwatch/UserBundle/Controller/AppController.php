<?php

namespace Overwatch\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * AppController
 * Renders the initial view to pass frontend off to AngularJS
 */
class AppController {
    /**
     * @Route("")
     * @Template("::base_angular.html.twig")
     */
    public function indexAction() {
        return [];
    }
}
