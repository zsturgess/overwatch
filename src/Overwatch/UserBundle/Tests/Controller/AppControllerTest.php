<?php

namespace Overwatch\UserBundle\Tests\Controller;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
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
    
    protected function logIn($role) {
        $session = $this->client->getContainer()->get('session');
        $firewall = 'overwatch';
        
        $token = new UsernamePasswordToken('testUser', null, $firewall, [$role]);
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
