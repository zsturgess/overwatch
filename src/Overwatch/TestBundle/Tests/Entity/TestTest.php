<?php

namespace Overwatch\TestBundle\Tests\Entity;

use Overwatch\TestBundle\Entity\Test;

/**
 * TestTest
 * A unit test for the Test Entity. Test!
 */
class TestTest extends \PHPUnit_Framework_TestCase {
    private $test;
    
    const TEST_NAME = "TestTest! Testing!";
    const TEST_ACTUAL = "1.2.3.4";
    const TEST_EXPECTATION = "toBeAnAwesomeIP";
    
    public function setUp() {
        $this->test = new Test;
        $this->test
            ->setName(self::TEST_NAME)
            ->setActual(self::TEST_ACTUAL)
            ->setExpectation(self::TEST_EXPECTATION)
            ->setCreatedAt()
            ->setUpdatedAt()
        ;
    }
    
    public function testValid() {
        $this->assertEquals(self::TEST_NAME, $this->test->getName());
        $this->assertEquals(self::TEST_NAME, (string) $this->test);
        $this->assertEquals(self::TEST_ACTUAL, $this->test->getActual());
        $this->assertEquals(self::TEST_EXPECTATION, $this->test->getExpectation());
        $this->assertInstanceOf('\DateTime', $this->test->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->test->getUpdatedAt());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                "id" => NULL,
                "name" => self::TEST_NAME,
                "actual" => self::TEST_ACTUAL,
                "expectation" => self::TEST_EXPECTATION,
                "expected" => NULL,
                "result" => NULL,
                "createdAt" => $this->test->getCreatedAt()->getTimestamp(),
                "updatedAt" => $this->test->getUpdatedAt()->getTimestamp()
            ]), 
            json_encode($this->test)
        );
    }
    
    public function testCreatedAtIsImmutable() {
        $expected = $this->test->getCreatedAt();
        
        $this->test->setCreatedAt();
        $this->assertEquals($expected, $this->test->getCreatedAt());
    }
}
