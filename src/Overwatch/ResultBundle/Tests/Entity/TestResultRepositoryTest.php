<?php

namespace Overwatch\ResultBundle\Tests\Entity;

use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;
use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;
use Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures;

/**
 * TestResultRepository
 * Functional tests for TestResultRepository
 */
class TestResultRepositoryTest extends DatabaseAwareTestCase {
    /**
     * @var Overwatch\ResultBundle\Entity\TestResultRepository
     */
    private $repo;
    
    public function setUp() {
        parent::setUp();
        $this->repo = $this->em->getRepository("OverwatchResultBundle:TestResult");
    }
    
    public function testGetResults() {
        $results = $this->repo->getResults(["test" => TestFixtures::$tests['test-1']]);    
        
        $this->assertInternalType('array', $results);
        $this->assertCount(3, $results);
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-1'], $results);
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-2'], $results);
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-3'], $results);
    }
    
    public function testGetResultsPagination() {
        $results = $this->repo->getResults(["test" => TestFixtures::$tests['test-1']], 1);
        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-3'], $results);
        
        $results = $this->repo->getResults(["test" => TestFixtures::$tests['test-1']], 1, 2);
        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);
        $this->assertCollectionContainsObject(TestResultFixtures::$results['result-2'], $results);
    }
}
