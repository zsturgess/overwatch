<?php

namespace Overwatch\TestBundle\Tests\Entity;

use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;
use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;
use Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures;

/**
 * TestRepositoryTest
 * Functional tests for TestRepository
 */
class TestRepositoryTest extends DatabaseAwareTestCase {
    /**
     * @var Overwatch\ResultBundle\Entity\TestResultRepository
     */
    private $repo;
    
    public function setUp() {
        parent::setUp();
        $this->repo = $this->em->getRepository("OverwatchTestBundle:Test");
    }
    
    public function testFindTests() {
        $results = $this->repo->findTests([
            TestFixtures::$tests['test-3']->getName(),
            TestGroupFixtures::$groups['group-1']->getName()
        ]);
        
        $this->assertCount(3, $results);
        $this->assertCollectionContainsObject(
            $this->repo->find(TestFixtures::$tests['test-1']->getId()),
            $results
        );
        $this->assertCollectionContainsObject(
            $this->repo->find(TestFixtures::$tests['test-2']->getId()),
            $results
        );
        $this->assertCollectionContainsObject(
            $this->repo->find(TestFixtures::$tests['test-3']->getId()),
            $results
        );
    }
    
    public function testFindTestsEmptySearch() {
        $this->assertEquals($this->repo->findAll(), $this->repo->findTests());
    }
    
    public function testFindTestsCorrectsNonArrayParameter() {
        $this->assertEquals(
            $this->repo->findTests([TestFixtures::$tests['test-3']->getName()]), 
            $this->repo->findTests(TestFixtures::$tests['test-3']->getName())
        );
    }
    
    public function testFindByName() {
        $name = TestFixtures::$tests['test-1']->getName();
        
        $this->assertEquals(
            $this->repo->findOneBy(["name" => $name]),
            $this->repo->findByName($name)
        );
    }
    
    public function testFindByGroupName() {
        $group = TestGroupFixtures::$groups['group-1'];
        
        $this->assertEquals(
            $this->repo->findBy(["group" => $group]),
            $this->repo->findByGroupName($group->getName())
        );
    }
    
    public function testFindByGroupNameInvalidGroup() {
        $this->assertEquals(
            [],
            $this->repo->findByGroupName("IfThisGroupExistsIBrokeIt")
        );
    }
}
