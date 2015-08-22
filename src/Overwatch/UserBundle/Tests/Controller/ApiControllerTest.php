<?php

namespace Overwatch\UserBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Overwatch\UserBundle\DataFixtures\ORM\UserFixtures;
use Overwatch\UserBundle\Enum\AlertSetting;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;

/**
 * ApiControllerTest
 * Functional test for API method provided by ApiController
 */
class ApiControllerTest extends DatabaseAwareTestCase {
    public function testCreateUser() {
        $email = 'abc@example.com';
        
        $this->loginAs(UserFixtures::$users['user-1'], 'overwatchApi');
        $this->client = $this->makeClient(); //When using loginAs, we must re-create the client
        $this->client->enableProfiler();
        $this->client->request('POST', '/api/users/' . $email);
        
        $this->assertJsonResponse($this->client->getResponse());
        
        $user = $this->em->getRepository('Overwatch\UserBundle\Entity\User')->findOneBy([
            "email" => $email
        ]);
        
        $this->assertInstanceOf('Overwatch\UserBundle\Entity\User', $user);
        $this->assertEquals($email, $user->getUsername());
        $this->assertEquals($email, $user->getEmail());
        
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
        
        $message = $mailCollector->getMessages()[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('You have been invited to Overwatch', $message->getSubject());
        $this->assertEquals(UserFixtures::$users['user-1']->getEmail(), key($message->getFrom()));
        $this->assertEquals($email, key($message->getTo()));
    }
    
    public function testCreateUserInsufficentPerms() {
        $this->logIn("ROLE_ADMIN");
        $this->client->request('POST', '/api/users/abc@example.com');
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testGetAlertSettings() {
        $this->logIn("ROLE_USER");
        $this->client->request('GET', '/api/alertSettings');
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode(AlertSetting::getAll()),
            $this->getResponseContent(TRUE)
        );
    }
    
    public function testFindUser() {
        $this->logIn("ROLE_SUPER_ADMIN");
        $this->client->request('GET', '/api/users/' . UserFixtures::$users['user-1']);
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode($this->em->find(
                'Overwatch\UserBundle\Entity\User',
                UserFixtures::$users['user-1']
            )),
            $this->getResponseContent(TRUE)
        );
    }
    
    public function testFindUserInsufficentPerms() {
        $this->logIn("ROLE_ADMIN");
        $this->client->request('GET', '/api/users/' . UserFixtures::$users['user-1']);
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testFindUserInvalidUser() {
        $this->logIn("ROLE_SUPER_ADMIN");
        $this->client->request('GET', '/api/users/example@example.org');
        
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
    
    public function testGetAllUsers() {
        $this->logIn("ROLE_SUPER_ADMIN");
        $this->client->request('GET', '/api/users');
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode($this->em->getRepository(
                'Overwatch\UserBundle\Entity\User'
            )->findAll()),
            $this->getResponseContent(TRUE)
        );
    }
    
    public function testGetAllUsersInsufficentPerms() {
        $this->logIn("ROLE_ADMIN");
        $this->client->request('GET', '/api/users');
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testSetAlertSetting() {
        $this->loginAs(
            $this->em->find(
                'Overwatch\UserBundle\Entity\User',
                UserFixtures::$users['user-2']->getId()
            ),
            'overwatchApi'
        );
        $this->client = $this->makeClient(); //When using loginAs, we must re-create the client
        $this->client->request('POST', '/api/users/alertSetting/1');
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertEquals(
            1,
            $this->em->find('Overwatch\UserBundle\Entity\User', UserFixtures::$users['user-2']->getId())->getAlertSetting()
        );
    }
    
    public function testToggleLockUser() {
        $this->loginAs(UserFixtures::$users['user-1'], 'overwatchApi');
        $this->client = $this->makeClient(); //When using loginAs, we must re-create the client
        $this->client->request('POST', '/api/users/' . UserFixtures::$users['user-2']->getId() . '/lock');
        
        $user = $this->em->find('Overwatch\UserBundle\Entity\User', UserFixtures::$users['user-2']->getId());
        $this->assertTrue($user->isLocked());
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode($user),
            $this->getResponseContent(TRUE)
        );
    }
    
    public function testToggleLockUserInsufficentPerms() {
        $this->logIn("ROLE_ADMIN");
        $this->client->request('POST', '/api/users/' . UserFixtures::$users['user-1']->getId() . '/lock');
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testToggleLockUserDisallowSelf() {
        $this->loginAs(UserFixtures::$users['user-1'], 'overwatchApi');
        $this->client = $this->makeClient(); //When using loginAs, we must re-create the client
        $this->client->request('POST', '/api/users/' . UserFixtures::$users['user-1']->getId() . '/lock');
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testSetUserRole() {
        $this->loginAs(UserFixtures::$users['user-1'], 'overwatchApi');
        $this->client = $this->makeClient(); //When using loginAs, we must re-create the client
        $this->client->request('POST', '/api/users/' . UserFixtures::$users['user-2']->getId() . '/role/ROLE_ADMIN');
        
        $user = $this->em->find('Overwatch\UserBundle\Entity\User', UserFixtures::$users['user-2']->getId());
        $this->assertTrue($user->hasRole("ROLE_ADMIN"));
        
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(
            json_encode($user),
            $this->getResponseContent(TRUE)
        );
    }
    
    public function testSetUserRoleInsufficentPerms() {
        $this->logIn("ROLE_ADMIN");
        $this->client->request('POST', '/api/users/' . UserFixtures::$users['user-1']->getId() . '/role/ROLE_USER');
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testSetUserRoleDisallowSelf() {
        $this->loginAs(UserFixtures::$users['user-1'], 'overwatchApi');
        $this->client = $this->makeClient(); //When using loginAs, we must re-create the client
        $this->client->request('POST', '/api/users/' . UserFixtures::$users['user-1']->getId() . '/role/ROLE_USER');
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testDeleteUser() {
        $this->loginAs(UserFixtures::$users['user-1'], 'overwatchApi');
        $this->client = $this->makeClient(); //When using loginAs, we must re-create the client
        $this->client->request('DELETE', '/api/users/' . UserFixtures::$users['user-2']->getId());
        
        $user = $this->em->find('Overwatch\UserBundle\Entity\User', UserFixtures::$users['user-2']->getId());
        $this->assertNull($user);
        
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }
    
    public function testDeleteUserInsufficentPerms() {
        $this->logIn("ROLE_ADMIN");
        $this->client->request('DELETE', '/api/users/' . UserFixtures::$users['user-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
    
    public function testDeleteUserDisallowSelf() {
        $this->loginAs(UserFixtures::$users['user-1'], 'overwatchApi');
        $this->client = $this->makeClient(); //When using loginAs, we must re-create the client
        $this->client->request('DELETE', '/api/users/' . UserFixtures::$users['user-1']->getId());
        
        $this->assertForbidden($this->client->getResponse());
    }
}
