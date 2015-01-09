<?php

namespace Overwatch\UserBundle\Tests\Base;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * DatabaseAwareTestCase
 *
 * @author Zac Sturgess <zac.sturgess@wearetwogether.com>
 */
class DatabaseAwareTestCase extends WebTestCase {
    use ApiTestHelperTrait;
    
    protected $em;
    protected $client;
    
    public function setUp() {
        $this->client = static::createClient();
        
        $this->loadFixtures([
            'Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures',
            'Overwatch\TestBundle\DataFixtures\ORM\TestFixtures',
            'Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures',
            'Overwatch\UserBundle\DataFixtures\ORM\UserFixtures'
        ]);
        
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }
}
