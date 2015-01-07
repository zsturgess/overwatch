<?php

namespace Overwatch\ExpectationBundle\Tests\Exception;

use Overwatch\ExpectationBundle\Exception\ExpectationNotFoundException;

/**
 * ExpectationNotFoundExceptionTest
 * Unit tests the constructor functionality of ExpectationNotFoundException
 */
class ExpectationNotFoundExceptionTest extends \PHPUnit_Framework_TestCase {
    public function testConstructor() {
        $badAlias = "toBeAnExpectationThatDoesntExist";
        $exception = new ExpectationNotFoundException($badAlias);
        
        $this->assertInstanceOf('\OutOfBoundsException', $exception);
        $this->assertEquals($exception->getMessage(), "Expectation with $badAlias could not be found.");
    }
}
