<?php

namespace Overwatch\ServiceBundle\Tests\Expectation;

use Overwatch\ServiceBundle\Expectation\ToRespondHttpExpectation;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * ToRespondHttpExpectationTest
 */
class ToRespondHttpExpectationTest extends \PHPUnit_Framework_TestCase {
    public function testExpectation() {
        $expectation = $this->createExpectationWithMockedResponse(200);
        
        $this->assertEquals("Responded HTTP 200 OK", $expectation->run("https://github.com/zsturgess/overwatch"));
    }
    
    public function testExpectationWithGivenStatus() {
        $this->assertEquals("Responded HTTP 200 OK", $this->createExpectationWithMockedResponse(200)->run("https://github.com/zsturgess/overwatch", 200));
    }
    
    public function testExpectationWithInvalidGivenStatus() {
        $this->assertEquals("Responded HTTP 200 OK", $this->createExpectationWithMockedResponse(200)->run("https://github.com/zsturgess/overwatch", "LS"));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The actual value provided is not a valid URL
     */
    public function testExpectationInvalidUrl() {
        $this->createExpectationWithMockedResponse(200)->run("8.8.8.8");
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage Expected https://github.com/zsturgess/overwatch to respond HTTP 200, actually responded HTTP 404
     */
    public function testExpectationFailsWithGivenStatus() {
        $this->createExpectationWithMockedResponse(404)->run("https://github.com/zsturgess/overwatch", 200);
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage https://github.com/zsturgess/overwatch responded HTTP 404, which is configured as a failed result
     */
    public function testExpectationFails() {
        $this->createExpectationWithMockedResponse(404)->run("https://github.com/zsturgess/overwatch");
    }
    
    /**
     * @covers Overwatch\ServiceBundle\Expectation\ToRespondHttpExpectation
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage https://github.com/zsturgess/overwatch responded HTTP 404, which is configured as a failed result
     */
    public function testExpectationFailsWithInvalidGivenStatus() {
        $this->createExpectationWithMockedResponse(404)->run("https://github.com/zsturgess/overwatch", "LS");
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage Expected https://void.example.com/ to respond HTTP , actually failed to respond
     * @todo Find a way of mocking a response failure that doesn't rely on a hostname not existing!
     */
    public function testExpectationFailsOnNoResponse() {
        $expectation = new ToRespondHttpExpectation([
            "allowable_codes" => [200, 201, 204, 206, 304],
            "unsatisfactory_codes" => [301, 302, 307, 308],
            "timeout" => 5
        ]);
        
        $expectation->run("https://void.example.com/");
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationUnsatisfactoryException
     * @expectedExceptionMessage https://github.com/zsturgess/overwatch responded HTTP 301, which is configured as unsatisfactory
     */
    public function testExpectationUnsatsifactory() {
        $this->createExpectationWithMockedResponse(301)->run("https://github.com/zsturgess/overwatch");
    }
    
    private function createExpectationWithMockedResponse($result) {
        $mock = new MockHandler([
            new Response($result, ['Content-Length' => 0]),
        ]);

        $handler = HandlerStack::create($mock);
        
        return new ToRespondHttpExpectation([
            "allowable_codes" => [200, 201, 204, 206, 304],
            "unsatisfactory_codes" => [301, 302, 307, 308],
            "timeout" => 5
        ], ['handler' => $handler]);
    }
}
