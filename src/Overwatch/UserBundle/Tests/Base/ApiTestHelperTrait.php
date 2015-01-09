<?php

namespace Overwatch\UserBundle\Tests\Base;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Overwatch\UserBundle\Entity\User;

/**
 * ApiTestHelperTrait
 */
trait ApiTestHelperTrait {
    public function assertCollectionContainsObject($object, $collection) {
        return $this->assertContains(json_encode($object), json_encode($collection));
    }
    
    public function assertJsonResponse($response) {
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
    }
    
    protected function logIn($role) {
        $session = $this->client->getContainer()->get('session');
        $firewall = 'overwatch';
        
        $token = new UsernamePasswordToken('admin', null, $firewall, [$role]);
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
    
    protected function getResponseContent($asJson = FALSE) {
        $response = $this->client->getResponse()->getContent();
        
        if ($asJson === FALSE) {
            return json_decode($response);
        }
        
        return $response;
    }
}
