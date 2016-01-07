<?php

namespace Overwatch\TestBundle\Tests\Entity;

use Overwatch\TestBundle\Entity\Test;
use Overwatch\TestBundle\Entity\TestGroup;

/**
 * TestGroupTest
 * A unit test for the TestGroup Entity.
 */
class TestGroupTest extends \PHPUnit_Framework_TestCase
{
    private $group;
    
    const GROUP_NAME = 'TestGroup! Testing!';
    
    public function setUp()
    {
        $this->group = new TestGroup;
        $this->group
            ->setName(self::GROUP_NAME)
            ->setCreatedAt()
            ->setUpdatedAt()
        ;
    }
    
    public function testValid()
    {
        $this->assertEquals(self::GROUP_NAME, $this->group->getName());
        $this->assertInstanceOf('\DateTime', $this->group->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->group->getUpdatedAt());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'id'        => null,
                'name'      => self::GROUP_NAME,
                'tests'     => [],
                'users'     => [],
                'createdAt' => $this->group->getCreatedAt()->getTimestamp(),
                'updatedAt' => $this->group->getUpdatedAt()->getTimestamp()
            ]),
            json_encode($this->group)
        );
    }
    
    public function testCreatedAtIsImmutable()
    {
        $expected = $this->group->getCreatedAt();
        
        $this->group->setCreatedAt();
        $this->assertEquals($expected, $this->group->getCreatedAt());
    }
    
    public function testMinimallySatisfiesGroupInterface()
    {
        $this->assertInstanceOf('\FOS\UserBundle\Model\GroupInterface', $this->group);
        $this->assertEquals([], $this->group->getRoles());
        $this->assertFalse($this->group->hasRole('ANYTHING'));
        
        $this->assertEquals($this->group, $this->group->addRole('SOMETHING'));
        $this->assertEquals([], $this->group->getRoles());
        
        $this->assertEquals($this->group, $this->group->removeRole('WHATEVER'));
        $this->assertEquals([], $this->group->getRoles());
        
        $this->assertEquals($this->group, $this->group->setRoles(['A_B_C']));
        $this->assertEquals([], $this->group->getRoles());
    }
    
    public function testAddRemoveTests()
    {
        $test1 = $this->createTest('Test 1');
        $test2 = $this->createTest('TestTwo');
        
        $this->group->addTest($test1);
        $this->assertCount(1, $this->group->getTests());
        $this->assertContains($test1, $this->group->getTests());
        
        $this->group->addTest($test2);
        $this->assertCount(2, $this->group->getTests());
        $this->assertContains($test2, $this->group->getTests());
        
        $this->group->removeTest($test1);
        $this->assertCount(1, $this->group->getTests());
        $this->assertNotContains($test1, $this->group->getTests());
    }
    
    private function createTest($name)
    {
        $test = new Test;
        $test->setName($name)
            ->setActual('8.8.8.8')
            ->setExpectation('toPing');
        
        return $test;
    }
}
