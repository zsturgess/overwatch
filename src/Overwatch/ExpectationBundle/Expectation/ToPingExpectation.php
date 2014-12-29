<?php

namespace Overwatch\ExpectationBundle\Expectation;

use Overwatch\ExpectationBundle\Helper\ExpectationInterface;
use Overwatch\ExpectationBundle\Exception as Result;

/**
 * ToPingExpectation
 * Expectation classes are the actual runners of tests.
 * This is the runner for the "toPing" expectation.
 */
class ToPingExpectation implements ExpectationInterface {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function run($actual, $expected = NULL) {
        //From http://php.net/manual/en/function.socket-create.php#101012
        $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        $socket  = socket_create(AF_INET, SOCK_RAW, 1);
        
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $this->config["timeout"], 'usec' => 0));
        socket_connect($socket, $actual, null);
        
        $sent = microtime(true);
        socket_send($socket, $package, strLen($package), 0);
        
        if (socket_read($socket, 255)) {
            $rtt = microtime(true) - $sent;
            socket_close($socket);
            
            if ($rtt > $this->config["unsatisfactory"]) {
                throw new Result\ExpectationUnsatisfactoryException("$actual responded in $rtt s, above the unsatisfactory threshold (" . $this->config["unsatisfactory"] . " s)");
            }
            
            return "Pinged in " . $rtt . "s";
        }
        
        socket_close($socket);
        throw new Result\ExpectationFailedException("$actual failed to respond in the timeout threshold (" . $this->config["timeout"] . " s)");
    }
}
