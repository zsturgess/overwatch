<?php

namespace Overwatch\ServiceBundle\Expectation;

use GuzzleHttp\Client as HttpClient;
use Overwatch\ExpectationBundle\Exception as Result;
use Overwatch\ExpectationBundle\Expectation\ExpectationInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * ToContainTextExpectation
 * Expectation that takes a plain string or regular expression. If the document
 * has a DOM, each element's textual content will be checked for 
 */
class ToContainTextExpectation implements ExpectationInterface
{
    private $expectationConfig;
    private $httpClient;

    public function __construct(array $expectationConfig, array $httpClientConfig = [])
    {
        $this->expectationConfig = $expectationConfig;
        $this->httpClient = new HttpClient($httpClientConfig);
    }

    public function run($actual, $expected = '')
    {
        if (!filter_var($actual, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            throw new \InvalidArgumentException('The actual value provided is not a valid URL');
        }

        $response = $this->httpClient->get(
            $actual,
            [
                'allow_redirects' => false,
                'http_errors' => !$this->expectationConfig['allow_errors'],
                'timeout' => $this->expectationConfig['timeout']
            ]
        );

        if (in_array($response->getHeader('Content-Type')[0], $this->expectationConfig['crawlable_types'])) {
            if (!$this->crawlResponse($response->getBody(), $expected)) {
                throw new Result\ExpectationFailedException(sprintf(
                    'Expected %s to contain the text "%s", but wasn\'t found in the textual content of any element',
                    $actual,
                    $expected
                ));
            }
        } elseif (!$this->findInString($response->getBody(), $expected)) {
            throw new Result\ExpectationFailedException(sprintf(
                'Expected %s to contain the text "%s", but wasn\'t found in the response',
                $actual,
                $expected
            ));
        }
        
        return sprintf(
            'Found the text "%s" on %s',
            $expected,
            $actual
        );
    }
    
    private function crawlResponse($response, $needle)
    {
        $crawler = new Crawler((string) $response);
        
        foreach ($crawler as $element) {
            if ($this->findInString($element->nodeValue, $needle)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function findInString($haystack, $needle)
    {
        $result = @preg_match($needle, $haystack);
        
        if ($result === false) {
            return (strstr($haystack, $needle) !== false);
        } else {
            return ($result === 1);
        }
    }
}
