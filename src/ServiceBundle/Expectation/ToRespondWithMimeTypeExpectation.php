<?php

namespace Overwatch\ServiceBundle\Expectation;

use GuzzleHttp\Client as HttpClient;
use Overwatch\ExpectationBundle\Exception as Result;
use Overwatch\ExpectationBundle\Expectation\ExpectationInterface;

/**
 * ToRespondWithMimeTypeExpectation
 * Expectation that a URL will respond to a HTTP request with the given mime type.
 */
class ToRespondWithMimeTypeExpectation implements ExpectationInterface
{
    private $expectationConfig;

    private $httpClient;

    public function __construct(array $expectationConfig, array $httpClientConfig = [])
    {
        $this->expectationConfig = $expectationConfig;
        $this->httpClient = new HttpClient($httpClientConfig);
    }

    public function run($actual, $expected = null)
    {
        $actual = filter_var($actual, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);

        if ($actual === false) {
            throw new \InvalidArgumentException('The actual value provided is not a valid URL');
        }

        $response = $this->httpClient->get(
            $actual,
            [
                'allow_redirects' => false,
                'http_errors'     => !$this->expectationConfig['allow_errors'],
                'timeout'         => $this->expectationConfig['timeout']
            ]
        );

        $result = $response->getHeader('Content-Type')[0];

        if ($expected !== $result) {
            throw new Result\ExpectationFailedException(sprintf(
                'Expected %s to respond with mime type "%s", actually responded "%s"',
                $actual,
                $expected,
                $result
            ));
        }

        return sprintf('Responded with mime type: "%s"', $result);
    }
}
