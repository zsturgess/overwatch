<?php

namespace Overwatch\ServiceBundle\Tests\Expectation;

use Overwatch\ServiceBundle\Expectation\ToRespondWithMimeTypeExpectation;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ToRespondWithMimeTypeExpectationTest extends \PHPUnit_Framework_TestCase
{
    public function testExpectation()
    {
        $expectation = $this->createExpectationWithMockedResponse('application/json');

        $actualResult = $expectation->run('http://someapi.test/endpoint.json', 'application/json');

        $this->assertEquals('Responded with mime type: "application/json"', $actualResult);
    }

    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage Expected http://someapi.test/endpoint.json to respond with mime type "application/json", actually responded "text/html"
     */
    public function testExpectationFails()
    {
        $this->createExpectationWithMockedResponse('text/html')
            ->run('http://someapi.test/endpoint.json', 'application/json');
    }

    private function createExpectationWithMockedResponse($resultMimeType)
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => $resultMimeType]),
        ]);

        $handler = HandlerStack::create($mock);

        return new ToRespondWithMimeTypeExpectation([], ['handler' => $handler]);
    }
}
