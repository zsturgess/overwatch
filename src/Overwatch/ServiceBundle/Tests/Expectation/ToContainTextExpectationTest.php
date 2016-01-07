<?php

namespace Overwatch\ServiceBundle\Tests\Expectation;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overwatch\ServiceBundle\Expectation\ToContainTextExpectation;

/**
 * ToContainTextExpectationTest
 */
class ToContainTextExpectationTest extends \PHPUnit_Framework_TestCase
{
    const URL = 'http://www.example.com/';
    const HAYSTACK = 'Trollface skeptical Fry wat me gusta';
    const NEEDLE = 'skep';
    const NEEDLE_REGEX = '/\bfr\w\b/i';
    const BAD_NEEDLE = 'norris';
    const BAD_NEEDLE_REGEX = '/wot\b/';
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The actual value provided is not a valid URL
     */
    public function testInvalidUrl()
    {
        $this->createExpectationWithMockedResponse()->run('8.8.8.8');
    }
    
    public function testBasicStringMatching()
    {
        $expectation = $this->createExpectationWithMockedResponse();
        
        $this->assertEquals(
            'Found the text "' . self::NEEDLE . '" on ' . self::URL,
            $expectation->run(self::URL, self::NEEDLE)
        );
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage Expected http://www.example.com/ to contain the text "norris", but wasn't found in the response
     */
    public function testFailedBasicMatch()
    {
        $expectation = $this->createExpectationWithMockedResponse();
        
        $expectation->run(self::URL, self::BAD_NEEDLE);
    }
    
    public function testRegexMatching()
    {
        $expectation = $this->createExpectationWithMockedResponse();
        
        $this->assertEquals(
            'Found the text "' . self::NEEDLE_REGEX . '" on ' . self::URL,
            $expectation->run(self::URL, self::NEEDLE_REGEX)
        );
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage Expected http://www.example.com/ to contain the text "/wot\b/", but wasn't found in the response
     */
    public function testFailedRegexMatch()
    {
        $expectation = $this->createExpectationWithMockedResponse();
        
        $expectation->run(self::URL, self::BAD_NEEDLE_REGEX);
    }
    
    public function testHtmlBasicStringMatching()
    {
        $expectation = $this->createExpectationWithMockedResponse(
            '<!DOCTYPE html><html><body><h1>Memes</h1><p>' . self::HAYSTACK . '</p></body></html>',
            'text/html'
        );
        
        $this->assertEquals(
            'Found the text "' . self::NEEDLE . '" on ' . self::URL,
            $expectation->run(self::URL, self::NEEDLE)
        );
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage Expected http://www.example.com/ to contain the text "norris", but wasn't found in the textual content of any element
     */
    public function testFailedHtmlBasicMatch()
    {
        $expectation = $this->createExpectationWithMockedResponse(
            '<!DOCTYPE html><html><body><h1>Memes</h1><p>' . self::HAYSTACK . '</p></body></html>',
            'text/html'
        );
        
        $expectation->run(self::URL, self::BAD_NEEDLE);
    }
    
    public function testHtmlErrorRegexMatching()
    {
        $expectation = $this->createExpectationWithMockedResponse(
            '<!DOCTYPE html><html><body><h1>Memes</h1><p>' . self::HAYSTACK . '</p></body></html>',
            'text/html',
            500,
            true
        );
        
        $this->assertEquals(
            'Found the text "' . self::NEEDLE_REGEX . '" on ' . self::URL,
            $expectation->run(self::URL, self::NEEDLE_REGEX)
        );
    }
    
    /**
     * @expectedException GuzzleHttp\Exception\ServerException
     * @expectedExceptionMessage Server error: `GET http://www.example.com/` resulted in a `500 Internal Server Error` response:
     */
    public function testHttpErrorWhenNotAllowed()
    {
        $expectation = $this->createExpectationWithMockedResponse(
            '<!DOCTYPE html><html><body><h1>Memes</h1><p>' . self::HAYSTACK . '</p></body></html>',
            'text/html',
            500
        );
        
        $expectation->run(self::URL, self::NEEDLE);
    }
    
    private function createExpectationWithMockedResponse($body = self::HAYSTACK, $contentType = 'text/plain', $result = 200, $allowErrors = false)
    {
        $mock = new MockHandler([
            new Response($result, ['Content-Type' => $contentType], $body),
        ]);

        $handler = HandlerStack::create($mock);
        
        return new ToContainTextExpectation([
            'allow_errors'    => $allowErrors,
            'crawlable_types' => ['text/html', 'text/xml'],
            'timeout'         => 5
        ], ['handler' => $handler]);
    }
}
