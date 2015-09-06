<?php

namespace Overwatch\ServiceBundle\Tests\Expectation;

use Overwatch\ServiceBundle\Expectation\ToResolveToExpectation;
use phpmock\phpunit\PHPMock;

/**
 * ToResolveToExpectationTest
 */
class ToResolveToExpectationTest extends \PHPUnit_Framework_TestCase {
    use PHPMock;
    
    private $expectation;
    
    public function setUp() {
        $this->createDnsMock();
        
        $this->expectation = new ToResolveToExpectation([
            "record_types" => ["A", "AAAA", "CNAME"]
        ]);
    }
    
    public function testExpectation() {
        $this->assertContains(
            " record that resolves to ",
            $this->expectation->run("console.aws.amazon.com", "lbr-optimized.console-l.amazonaws.com")
        );
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage actually resolves to 
     */
    public function testExpectationFailed() {
        $this->expectation->run("console.aws.amazon.com", "ns-921.amazon.com");
    }
    
    private function createDnsMock() {
        $mock = $this->getFunctionMock('Overwatch\ServiceBundle\Expectation', 'dns_get_record');
        $mock->expects($this->once())->willReturn([
            [
                "host"=>"console.aws.amazon.com",
                "class"=>"IN",
                "ttl"=>60,
                "type"=>"SOA",
                "mname"=>"ns-921.amazon.com",
                "rname"=>"root.amazon.com",
                "serial"=>1441402959,
                "refresh"=>3600,
                "retry"=>900,
                "expire"=>7776000,
                "minimum-ttl"=>60
                    
            ],
            [
                "host"=>"console.aws.amazon.com",
                "class"=>"IN",
                "ttl"=>108,
                "type"=>"NS",
                "target"=>"ns-921.amazon.com"
            ],
            [
                "host"=>"console.aws.amazon.com",
                "class"=>"IN",
                "ttl"=>60,
                "type"=>"CNAME",
                "target"=>"lbr-optimized.console-l.amazonaws.com"
            ]
        ]);
    }
}
