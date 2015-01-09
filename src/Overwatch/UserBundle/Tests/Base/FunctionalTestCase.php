<?php

namespace Overwatch\UserBundle\Tests\Base;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * BaseFunctionalTest
 *
 * @author Zac Sturgess <zac.sturgess@wearetwogether.com>
 */
class FunctionalTestCase extends WebTestCase {
    use ApiTestHelperTrait;
    
    protected $client = null;

    public function setUp() {
        $this->client = static::createClient();
    }
}
