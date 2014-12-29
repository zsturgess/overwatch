<?php

namespace Overwatch\ExpectationBundle\Expectation;

use Overwatch\ExpectationBundle\Helper\ExpectationInterface;
use Overwatch\ExpectationBundle\Exception as Result;

/**
 * ToResolveToExpectation
 * Expectation classes are the actual runners of tests.
 * This is the runner for the "toResolveTo" expectation.
 */
class ToResolveToExpectation implements ExpectationInterface {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function run($actual, $expected = NULL) {
        $dnsRecords = dns_get_record($actual);
        
        foreach ($dnsRecords as $dnsRecord) {
            if (!in_array($dnsRecord["type"], $this->config["record_types"])) {
                continue;
            }
            
            foreach (["mname", "txt", "target", "ipv6", "ip"] as $destination) {
                if (array_key_exists($destination, $dnsRecord)) {
                    $found = $dnsRecord[$destination];
                }
            }
            
            if ($found === $expected) {
                return $actual . " has a " . $dnsRecord["type"] . " record that resolves to " . $found;
            }
        }
        
        throw new Result\ExpectationFailedException("Expected $actual to resolve to $expected, actually resolves to $found");
    }
}
