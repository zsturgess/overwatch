<?php

namespace Overwatch\UserBundle\Tests\Base;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * BaseFunctionalTest
 *
 * @author Zac Sturgess <zac.sturgess@wearetwogether.com>
 */
class BaseFunctionalTest extends WebTestCase {
    private $client = null;

    public function setUp() {
        $this->client = static::createClient();
    }
    
    private function logIn($role) {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'overwatch';
        $token = new UsernamePasswordToken('admin', null, $firewall, [$role]);
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
