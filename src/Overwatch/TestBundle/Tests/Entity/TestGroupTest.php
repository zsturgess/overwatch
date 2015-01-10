<?php

namespace Overwatch\TestBundle\Tests\Entity;

use Overwatch\TestBundle\Entity\TestGroup;

/**
 * TestGroupTest
 * A unit test for the TestGroup Entity.
 */
class TestGroupTest extends \PHPUnit_Framework_TestCase {
    private $group;
    
    const GROUP_NAME = "TestGroup! Testing!";
    
    public function setUp() {
        $this->group = new TestGroup;
        $this->group
            ->setName(self::GROUP_NAME)
            ->setCreatedAt()
            ->setUpdatedAt()
        ;
    }
    
    public function testValid() {
        $this->assertEquals(self::GROUP_NAME, $this->group->getName());
        $this->assertInstanceOf('\DateTime', $this->group->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->group->getUpdatedAt());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                "id" => NULL,
                "name" => self::GROUP_NAME,
                "tests" => [],
                "users" => [],
                "createdAt" => $this->group->getCreatedAt()->getTimestamp(),
                "updatedAt" => $this->group->getUpdatedAt()->getTimestamp()
            ]), 
            json_encode($this->group)
        );
    }
    
    public function testCreatedAtIsImmutable() {
        $expected = $this->group->getCreatedAt();
        
        $this->group->setCreatedAt();
        $this->assertEquals($expected, $this->group->getCreatedAt());
    }
    
    public function testMinimallySatisfiesGroupInterface() {
        $this->assertInstanceOf('\FOS\UserBundle\Model\GroupInterface', $this->group);
        $this->assertEquals([], $this->group->getRoles());
        $this->assertFalse($this->group->hasRole("ANYTHING"));
        
        $this->assertEquals($this->group, $this->group->addRole("SOMETHING"));
        $this->assertEquals([], $this->group->getRoles());
        
        $this->assertEquals($this->group, $this->group->removeRole("WHATEVER"));
        $this->assertEquals([], $this->group->getRoles());
        
        $this->assertEquals($this->group, $this->group->setRoles(["A_B_C"]));
        $this->assertEquals([], $this->group->getRoles());
    }
}
