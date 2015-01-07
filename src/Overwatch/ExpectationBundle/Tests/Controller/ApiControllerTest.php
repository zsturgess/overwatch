<?php

namespace Overwatch\ExpectationBundle\Tests\Controller;

use Overwatch\UserBundle\Tests\Base\BaseFunctionalTest;

/**
 * ApiControllerTest
 * Functional test of API method provided by the APIController
 */
class ApiControllerTest extends BaseFunctionalTest {
    public function testGetAll() {
        $expectations = [
            "toPing",
            "toResolveTo"
        ];
        
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/api/expectations');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode($expectations), $this->client->getResponse()->getContent());
    }
}
