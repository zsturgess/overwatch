<?php

namespace Overwatch\UserBundle\Tests\Base;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * BaseFunctionalTest
 * Extends Symfony's WebTestCase with custom logic. Uses ApiTestHelperTrait.
 * For use when we don't need the full fixture functionality of DatabaseAwareTestCase
 */
class FunctionalTestCase extends WebTestCase
{
    use ApiTestHelperTrait;
    
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }
}
