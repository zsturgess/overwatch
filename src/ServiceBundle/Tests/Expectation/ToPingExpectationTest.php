<?php

namespace Overwatch\ServiceBundle\Tests\Expectation;

use Overwatch\ServiceBundle\Expectation\ToPingExpectation;
use phpmock\phpunit\PHPMock;

/**
 * ToPingExpectationTest
 */
class ToPingExpectationTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;
    
    private $expectation;
    
    public function setUp()
    {
        $this->createSocketMocks();
        
        $this->expectation = new ToPingExpectation([
            'timeout'        => 2,
            'unsatisfactory' => 1
        ]);
    }
    
    public function testExpectation()
    {
        $this->createSocketReadMock();
        
        $this->assertContains(
            'Pinged in ',
            $this->expectation->run('8.8.8.8')
        );
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationUnsatisfactoryException
     * @expectedExceptionMessage above the unsatisfactory threshold 
     */
    public function testExpectationUnsatisfactory()
    {
        $expectation = new ToPingExpectation([
            'timeout'        => 2,
            'unsatisfactory' => 0.2
        ]);
        $this->createSocketReadMock('delayed');
        $expectation->run('8.8.8.8');
    }
    
    /**
     * @expectedException Overwatch\ExpectationBundle\Exception\ExpectationFailedException
     * @expectedExceptionMessage failed to respond in the timeout threshold 
     */
    public function testExpectationFailed()
    {
        $this->createSocketReadMock('failed');
        $this->expectation->run('8.8.8.8');
    }
    
    private function createSocketMocks()
    {
        foreach ([
            'socket_create',
            'socket_set_option',
            'socket_connect',
            'socket_send',
            'socket_close'
        ] as $func) {
            $mock = $this->getFunctionMock('Overwatch\ServiceBundle\Expectation', $func);
            $mock->expects($this->once())->willReturn(true);
        }
    }
    
    private function createSocketReadMock($type = 'normal')
    {
        $mock = $this->getFunctionMock('Overwatch\ServiceBundle\Expectation', 'socket_read');
        
        switch ($type) {
            case 'normal':
                $mock->expects($this->once())->willReturn(true);
                break;
            
            case 'delayed':
                $mock->expects($this->once())->willReturnCallback(function() {
                    usleep(210000);
                    return true;
                });
                break;
            
            case 'failed':
                $mock->expects($this->once())->willReturn(false);
                break;
        }
    }
}
