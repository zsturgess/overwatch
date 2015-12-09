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
    
    public function testExpectationWithAllowedHttpErrors()
    {
        $expectation = $this->createExpectationWithMockedResponse('application/json', 500, true);

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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The actual value provided is not a valid URL
     */
    public function testExpectationWithInvalidUrl()
    {
        $this->createExpectationWithMockedResponse('text/html')
            ->run('invalid', 'text/html');
    }

    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     */
    public function testExpectationWithBadResponse()
    {
        $this->createExpectationWithMockedResponse('text/html', 500)
            ->run('http://someapi.test/endpoint.json', 'text/html');
    }

    private function createExpectationWithMockedResponse($resultMimeType, $httpCode = 200, $httpErrors = false)
    {
        $mock = new MockHandler([
            new Response($httpCode, ['Content-Type' => $resultMimeType]),
        ]);

        $handler = HandlerStack::create($mock);

        return new ToRespondWithMimeTypeExpectation([
            'allow_errors' => $httpErrors,
            'timeout' => 5
        ], ['handler' => $handler]);
    }
}
