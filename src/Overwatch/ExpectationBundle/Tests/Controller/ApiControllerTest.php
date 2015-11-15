<?php

namespace Overwatch\ExpectationBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Overwatch\UserBundle\Tests\Base\FunctionalTestCase;

/**
 * ApiControllerTest
 * Functional test of API method provided by the APIController
 */
class ApiControllerTest extends FunctionalTestCase {
    public function testGetAll() {
        $expectations = [
            "toPing",
            "toResolveTo",
            "toRespondHttp",
            "toRespondWithMimeType",
        ];
        
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/api/expectations');

        $this->assertJsonResponse($this->client->getResponse());
        $this->assertJsonStringEqualsJsonString(json_encode($expectations), $this->client->getResponse()->getContent());
    }
    
    public function testGetAllInsufficentPerms() {
        $this->logIn('ROLE_USER');
        $this->client->request('GET', '/api/expectations');
        
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
}
