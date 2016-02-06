<?php

namespace Overwatch\TestBundle\Tests\Controller;

use Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures;
use Overwatch\TestBundle\Entity\TestGroup;
use Overwatch\UserBundle\DataFixtures\ORM\UserFixtures;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * TestGroupApiControllerTest
 * Functional test of API methods provided by the APIController
 */
class TestGroupApiControllerTest extends DatabaseAwareTestCase
{
    public function testCreateGroup()
    {
        $newGroupName = 'New Group 1';
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->makeJsonRequest(
            'POST',
            '/api/groups',
            [
                'name' => $newGroupName,
            ]
        );
        
        $group = $this->em->getRepository("Overwatch\TestBundle\Entity\TestGroup")->findOneBy([
            'name' => $newGroupName
        ]);
        
        $this->assertNotNull($group);
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertEquals($newGroupName, $this->getResponseContent()->name);
    }
    
    public function testCreateGroupInsufficentPerms()
    {
        $newGroupName = 'New Group 2';
        
        $this->logIn('ROLE_ADMIN');
        $this->makeJsonRequest(
            'POST',
            '/api/groups',
            [
                'name' => $newGroupName,
            ]
        );
        
        $group = $this->em->getRepository("Overwatch\TestBundle\Entity\TestGroup")->findOneBy([
            'name' => $newGroupName
        ]);
        
        $this->assertNull($group);
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testCreateGroupNoName()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->makeJsonRequest(
            'POST',
            '/api/groups',
            []
        );
        
        $group = $this->em->getRepository("Overwatch\TestBundle\Entity\TestGroup")->findOneBy([
            'name' => null
        ]);
        
        $this->assertNull($group);
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }
    
    public function testGetAllGroups()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/groups');
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertCount(3, $this->getResponseContent());
        $this->assertCollectionContainsObject(
            $this->em->find("Overwatch\TestBundle\Entity\TestGroup", TestGroupFixtures::$groups['group-1']->getId()),
            $this->getResponseContent()
        );
        $this->assertCollectionContainsObject(
            $this->em->find("Overwatch\TestBundle\Entity\TestGroup", TestGroupFixtures::$groups['group-2']->getId()),
            $this->getResponseContent()
        );
    }
    
    public function testGetAllGroupsAsUser()
    {
        $this->loginAs(
            $this->em->find("Overwatch\UserBundle\Entity\User", UserFixtures::$users['user-2']->getId()),
            'overwatch'
        );
        $this->client = $this->makeClient(); //When using loginAs, we must use a new client
        $this->client->request('GET', '/api/groups');
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertCount(1, $this->getResponseContent());
        $this->assertCollectionContainsObject(
            $this->em->find("Overwatch\TestBundle\Entity\TestGroup", TestGroupFixtures::$groups['group-1']->getId()),
            $this->getResponseContent()
        );
    }
    
    public function testGetGroup()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/groups/' . TestGroupFixtures::$groups['group-1']->getId());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $this->em->find("Overwatch\TestBundle\Entity\TestGroup", TestGroupFixtures::$groups['group-1']->getId())
            ),
            $this->getResponseContent(true)
        );
    }
    
    public function testGetGroupInsufficentPerms()
    {
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/api/groups/' . TestGroupFixtures::$groups['group-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testGetGroupInvalidGroup()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('GET', '/api/groups/1000');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testUpdateGroup()
    {
        $newName = 'Renamed Group';
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->makeJsonRequest(
            'PUT',
            '/api/groups/' . TestGroupFixtures::$groups['group-2']->getId(),
            [
                'name' => $newName
            ]
        );
        
        $group = $this->em->find("Overwatch\TestBundle\Entity\TestGroup", TestGroupFixtures::$groups['group-2']->getId());
        
        $this->assertEquals($newName, $group->getName());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode($group),
            $this->getResponseContent(true)
        );
    }
    
    public function testUpdateGroupInsufficentPerms()
    {
        $this->logIn('ROLE_USER');
        $this->client->request('PUT', '/api/groups/' . TestGroupFixtures::$groups['group-2']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testUpdateGroupInvalidGroup()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('PUT', '/api/groups/1000');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testDeleteGroup()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('DELETE', '/api/groups/' . TestGroupFixtures::$groups['group-3']->getId());
        
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        $this->assertNull($this->em->find("Overwatch\TestBundle\Entity\TestGroup", TestGroupFixtures::$groups['group-3']->getId()));
    }
    
    public function testDeleteGroupPopulatedGroup()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('DELETE', '/api/groups/' . TestGroupFixtures::$groups['group-2']->getId());
        
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertNotNull($this->em->find("Overwatch\TestBundle\Entity\TestGroup", TestGroupFixtures::$groups['group-2']->getId()));
    }
    
    public function testDeleteGroupInsufficentPerms()
    {
        $this->logIn('ROLE_ADMIN');
        $this->client->request('DELETE', '/api/groups/' . TestGroupFixtures::$groups['group-3']->getId());
        
        $this->assertForbidden($this->client->getResponse());
        $this->assertNotNull($this->em->find("Overwatch\TestBundle\Entity\TestGroup", TestGroupFixtures::$groups['group-2']->getId()));
    }
    
    public function testDeleteGroupInvalidGroup()
    {
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('DELETE', '/api/groups/1000');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAddUserToGroup()
    {
        $user = UserFixtures::$users['user-2'];
        $group = TestGroupFixtures::$groups['group-3'];
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('POST', '/api/groups/' . $group->getId() . '/user/' . $user->getId());
        
        $group = $this->em->find("Overwatch\TestBundle\Entity\TestGroup", $group->getId());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(json_encode($user), $this->getResponseContent(true));
        $this->assertCount(1, $group->getUsers());
    }
    
    public function testAddUserToGroupInsufficentPerms()
    {
        $user = UserFixtures::$users['user-2'];
        $group = TestGroupFixtures::$groups['group-3'];
        
        $this->logIn('ROLE_ADMIN');
        $this->client->request('POST', '/api/groups/' . $group->getId() . '/user/' . $user->getId());
        
        $group = $this->em->find("Overwatch\TestBundle\Entity\TestGroup", $group->getId());
        
        $this->assertForbidden($this->client->getResponse());
        $this->assertCount(0, $group->getUsers());
    }
    
    public function testAddUserToGroupInvalidGroup()
    {
        $user = UserFixtures::$users['user-2'];
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('POST', '/api/groups/1000/user/' . $user->getId());
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAddUserToGroupInvalidUser()
    {
        $group = TestGroupFixtures::$groups['group-3'];
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('POST', '/api/groups/' . $group->getId() . '/user/1000');
        
        $group = $this->em->find("Overwatch\TestBundle\Entity\TestGroup", $group->getId());
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertCount(0, $group->getUsers());
    }
    
    public function testRemoveUserFromGroup()
    {
        $user = UserFixtures::$users['user-2'];
        $group = TestGroupFixtures::$groups['group-1'];
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('DELETE', '/api/groups/' . $group->getId() . '/user/' . $user->getId());
        
        $group = $this->em->find("Overwatch\TestBundle\Entity\TestGroup", $group->getId());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(json_encode($user), $this->getResponseContent(true));
        $this->assertCount(1, $group->getUsers());
    }
    
    public function testRemoveUserFromGroupInsufficentPerms()
    {
        $user = UserFixtures::$users['user-2'];
        $group = TestGroupFixtures::$groups['group-1'];
        
        $this->logIn('ROLE_ADMIN');
        $this->client->request('DELETE', '/api/groups/' . $group->getId() . '/user/' . $user->getId());
        
        $group = $this->em->find("Overwatch\TestBundle\Entity\TestGroup", $group->getId());
        
        $this->assertForbidden($this->client->getResponse());
        $this->assertCount(2, $group->getUsers());
    }
    
    public function testRemoveUserFromGroupInvalidGroup()
    {
        $user = UserFixtures::$users['user-2'];
        $group = TestGroupFixtures::$groups['group-1'];
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('DELETE', '/api/groups/1000/user/' . $user->getId());
        
        $group = $this->em->find("Overwatch\TestBundle\Entity\TestGroup", $group->getId());
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertCount(2, $group->getUsers());
    }
    
    public function testRemoveUserFromGroupInvalidUser()
    {
        $group = TestGroupFixtures::$groups['group-1'];
        
        $this->logIn('ROLE_SUPER_ADMIN');
        $this->client->request('DELETE', '/api/groups/' . $group->getId() . '/user/1000');
        
        $group = $this->em->find("Overwatch\TestBundle\Entity\TestGroup", $group->getId());
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertCount(2, $group->getUsers());
    }
}
