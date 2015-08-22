<?php

namespace Overwatch\ServiceBundle\Expectation;

use GuzzleHttp\Client;
use Overwatch\ExpectationBundle\Exception as Result;
use Overwatch\ExpectationBundle\Expectation\ExpectationInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * ToResolveToExpectation
 * Expectation classes are the actual runners of tests.
 * This is the runner for the "toRespondHttp" expectation.
 */
class ToRespondHttpExpectation implements ExpectationInterface {
    private $config;
    private $client;
    
    public function __construct($config) {
        $this->config = $config;
        $this->client = new Client();
    }
    
    public function run($actual, $expected = NULL) {
        $actual = filter_var($actual, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);

        try {
            $response = $this->client->get(
                $actual,
                [
                    'allow_redirects' => false,
                    'timeout' => $this->config["timeout"]
                ]
            );
        } catch (\Exception $e) {
            //Transform exception under certain circumstances, else re-throw.
            if ($e instanceof \GuzzleHttp\Exception\RequestException && !$e->hasResponse()) {
                throw new Result\ExpectationFailedException("Expected $actual to respond HTTP $expected, actually failed to respond", 0, $e);
            }
            
            throw $e;
        }
        
        $result = $response->getStatusCode();
        
        if ($this->isValidStatusCode($expected)) {
            if ((int) $expected !== $result) {
                throw new Result\ExpectationFailedException("Expected $actual to respond HTTP $expected, actually responded HTTP $result");
            }
        } else {
            if (in_array($result, $this->config["unsatisfactory_codes"])) {
                throw new Result\ExpectationUnsatisfactoryException("$actual responded HTTP $result, which is configured as unsatisfactory");
            } else if (!in_array($result, $this->config["allowable_codes"])) {
                throw new Result\ExpectationFailedException("$actual responded HTTP $result, which is configured as a failed result");
            }
        }
        
        return "Responded HTTP $result " . $response->getReasonPhrase();
    }
    
    private function isValidStatusCode($code) {
        return in_array($code, array_keys(Response::$statusTexts));
    }
}
