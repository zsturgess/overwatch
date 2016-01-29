<?php

namespace Overwatch\TestBundle\Tests\Controller;

use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;
use Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures;
use Overwatch\TestBundle\Entity\Test;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * TestApiControllerTest
 * Functional test of API methods provided by the APIController
 */
class TestApiControllerTest extends DatabaseAwareTestCase
{
    public function testCreateTest()
    {
        $newTest = new Test;
        $newTest
            ->setName('Ping 1234')
            ->setActual('1.2.3.4')
            ->setExpectation('toPing')
        ;
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->makeJsonRequest(
            'POST',
            '/api/tests/group/' . TestGroupFixtures::$groups['group-2']->getId(),
            [
                'name'        => $newTest->getName(),
                'actual'      => $newTest->getActual(),
                'expectation' => $newTest->getExpectation()
            ]
        );
        
        $test = $this->em->getRepository("Overwatch\TestBundle\Entity\Test")->findOneBy([
            'name' => $newTest->getName()
        ]);
       
        $this->assertNotNull($test);
        $this->assertEquals($newTest->getActual(), $test->getActual());
        $this->assertEquals($newTest->getExpectation(), $test->getExpectation());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode($test),
            $this->getResponseContent(true)
        );
    }
    
    public function testCreateTestInsufficentPerms()
    {
        $this->logIn('ROLE_USER');
        $this->client->request('POST', '/api/tests/group/' . TestGroupFixtures::$groups['group-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testCreateTestInvalidExpectation()
    {
        $newTest = new Test;
        $newTest
            ->setName('Ping 1234')
            ->setActual('1.2.3.4')
            ->setExpectation('IfThisExpectationExistsIBrokeIt')
        ;
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->makeJsonRequest(
            'POST',
            '/api/tests/group/' . TestGroupFixtures::$groups['group-2']->getId(),
            [
                'name'        => $newTest->getName(),
                'actual'      => $newTest->getActual(),
                'expectation' => $newTest->getExpectation()
            ]
        );
        
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertContains($newTest->getExpectation(), $this->getResponseContent(true));
        $this->assertContains('could not be found', $this->getResponseContent(true));
        
        $this->assertNull($this->em->getRepository("Overwatch\TestBundle\Entity\Test")->findOneBy([
            'name' => $newTest->getName()
        ]));
    }
    
    public function testCreateTestInvalidActual()
    {
        $newTest = new Test;
        $newTest
            ->setName('Ping 1234')
            ->setExpectation('toPing')
        ;
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->makeJsonRequest(
            'POST',
            '/api/tests/group/' . TestGroupFixtures::$groups['group-2']->getId(),
            [
                'name'        => $newTest->getName(),
                'expectation' => $newTest->getExpectation()
            ]
        );
        
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertContains('An actual value to test against must be provided.', $this->getResponseContent(true));
        
        $this->assertNull($this->em->getRepository("Overwatch\TestBundle\Entity\Test")->findOneBy([
            'name' => $newTest->getName()
        ]));
    }
    
    public function testGetTestsInGroup()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/tests/group/' . TestGroupFixtures::$groups['group-1']->getId());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertCount(2, $this->getResponseContent());
        $this->assertCollectionContainsObject(
            $this->em->find("Overwatch\TestBundle\Entity\Test", TestFixtures::$tests['test-1']->getId()),
            $this->getResponseContent()
        );
        $this->assertCollectionContainsObject(
            $this->em->find("Overwatch\TestBundle\Entity\Test", TestFixtures::$tests['test-2']->getId()),
            $this->getResponseContent()
        );
    }
    
    public function testGetTestsInGroupInsufficentPerms()
    {
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/api/tests/group/' . TestGroupFixtures::$groups['group-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testGetTestsInGroupInvalidGroup()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/tests/group/1000');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testGetTest()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/tests/' . TestFixtures::$tests['test-1']->getId());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $this->em->find("Overwatch\TestBundle\Entity\Test", TestFixtures::$tests['test-1']->getId())
            ),
            $this->getResponseContent(true)
        );
    }
    
    public function testGetTestInsufficentPerms()
    {
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/api/tests/' . TestFixtures::$tests['test-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testGetTestInvalidTest()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/tests/1000');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testUpdateTest()
    {
        $newName = 'Renamed Test';
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->makeJsonRequest(
            'PUT',
            '/api/tests/' . TestFixtures::$tests['test-1']->getId(),
            [
                'name' => $newName
            ]
        );
        
        $test = $this->em->find("Overwatch\TestBundle\Entity\Test", TestFixtures::$tests['test-1']->getId());
        
        $this->assertEquals($newName, $test->getName());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode($test),
            $this->getResponseContent(true)
        );
    }
    
    public function testUpdateTestInsufficentPerms()
    {
        $this->logIn('ROLE_USER');
        $this->client->request('PUT', '/api/tests/' . TestFixtures::$tests['test-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testUpdateTestInvalidTest()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('PUT', '/api/tests/1000');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testDeleteTest()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('DELETE', '/api/tests/' . TestFixtures::$tests['test-1']->getId());
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        $this->assertNull($this->em->find("Overwatch\TestBundle\Entity\Test", TestFixtures::$tests['test-1']->getId()));
    }
    
    public function testDeleteTestInsufficentPerms()
    {
        $this->logIn('ROLE_USER');
        $this->client->request('DELETE', '/api/tests/' . TestFixtures::$tests['test-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testDeleteTestInvalidTest()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('DELETE', '/api/tests/1000');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testRunTest()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('POST', '/api/tests/' . TestFixtures::$tests['test-1']->getId());
        
        $test = $this->em->find("Overwatch\TestBundle\Entity\Test", TestFixtures::$tests['test-1']->getId());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode($test->getResults()->last()),
            $this->getResponseContent(true)
        );
    }
    
    public function testRunTestInsufficentPerms()
    {
        $this->logIn('ROLE_USER');
        $this->client->request('POST', '/api/tests/' . TestFixtures::$tests['test-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testRunTestInvalidTest()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('POST', '/api/tests/1000');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
}
