<?php

namespace Overwatch\UserBundle\Tests\Controller;

use Overwatch\UserBundle\Tests\Base\FunctionalTestCase;

/**
 * AppControllerTest
 * Functional test for the index route provided by AppController
 */
class AppControllerTest extends FunctionalTestCase {
    public function testIndexPage() {
        $this->logIn("ROLE_USER");
        $this->client->request('GET', '/');
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertContains("<div data-ng-view>", $this->getResponseContent(TRUE));
        $this->assertNotContains('<i class="icon-users"></i> Manage Users', $this->getResponseContent(TRUE));
    }
    
    public function testIndexPageAsSuperAdmin() {
        $this->logIn("ROLE_SUPER_ADMIN");
        $this->client->request('GET', '/');
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertContains("<div data-ng-view>", $this->getResponseContent(TRUE));
        $this->assertContains('<i class="icon-users"></i> Manage Users', $this->getResponseContent(TRUE));
    }
}
