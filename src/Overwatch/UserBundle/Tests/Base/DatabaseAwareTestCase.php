<?php

namespace Overwatch\UserBundle\Tests\Base;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * DatabaseAwareTestCase
 *
 * @author Zac Sturgess <zac.sturgess@wearetwogether.com>
 */
class DatabaseAwareTestCase extends WebTestCase {
    protected $em;
    
    public function setUp() {
        $this->loadFixtures([
            'Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures',
            'Overwatch\TestBundle\DataFixtures\ORM\TestFixtures',
            'Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures'
        ]);
        
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }
    
    public function assertCollectionContainsObject($object, $collection) {
        return $this->assertContains(json_encode($object), json_encode($collection));
    }
}
