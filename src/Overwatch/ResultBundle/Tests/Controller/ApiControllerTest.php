<?php

namespace Overwatch\ResultBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures;
use Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures;
use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;

/**
 * ApiControllerTest
 * Functional test of API methods provided by the APIController
 */
class ApiControllerTest extends DatabaseAwareTestCase {
    public function testGetResults() {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/results');
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertCount(4, $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-1'], $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-2'], $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-3'], $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-4'], $this->getResponseContent());
    }
    
    public function testGetResultsInsufficentPerms() {
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/api/results');
        
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
    
    public function testGetRecentGroupResults() {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/results/group/' . TestGroupFixtures::$groups['group-1']->getId());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertCount(2, $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-3'], $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-4'], $this->getResponseContent());
    }
    
    public function testGetRecentGroupResultsInsufficentPerms() {
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/api/results/group/' . TestGroupFixtures::$groups['group-1']->getId());
        
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
    
    public function testGetResultsForTest() {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/results/test/' . TestFixtures::$tests['test-1']->getId());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertCount(3, $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-1'], $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-2'], $this->getResponseContent());
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-3'], $this->getResponseContent());
    }
    
    public function testGetResultsForTestInsufficentPerms() {
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/api/results/test/' . TestFixtures::$tests['test-1']->getId());
        
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
}
