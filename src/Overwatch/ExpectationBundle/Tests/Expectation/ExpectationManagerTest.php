<?php

namespace Overwatch\ExpectationBundle\Tests\Expectation;

use Overwatch\ExpectationBundle\Exception as ExpectationException;
use Overwatch\ExpectationBundle\Expectation\ExpectationManager;
use Overwatch\ResultBundle\Enum\ResultStatus;
use Overwatch\TestBundle\Entity\Test;

/**
 * ExpectationManagerTest
 * Unit tests the basic functionality of the ExpectationManager
 */
class ExpectationManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $em;

    protected $toPingMock;

    protected $test;

    protected function setUp()
    {
        $this->em = new ExpectationManager;

        $this->toPingMock = $this->getMockBuilder('Overwatch\ServiceBundle\Expectation\ToPingExpectation')
            ->setConstructorArgs([[
                'timeout'        => 1,
                'unsatisfactory' => 0.5
            ]])
            ->getMock();
    }

    private function setUpTestRun($result)
    {
        if ($result instanceof \Exception) {
            $this->toPingMock->expects($this->exactly(2))
                ->method('run')
                ->will($this->throwException($result));
        } elseif (is_array($result)) {
            $this->toPingMock->expects($this->exactly(2))
                ->method('run')
                ->will($this->onConsecutiveCalls($result[0], $result[1]));
        } else {
            $this->toPingMock->expects($this->once())
                ->method('run')
                ->will($this->returnValue($result));
        }

        $this->em->add($this->toPingMock, 'toPing');

        $this->test = new Test;
        $this->test
            ->setActual('8.8.8.8')
            ->setExpectation('toPing')
        ;
    }

    public function testAdd()
    {
        $this->em->add($this->toPingMock, 'toPing');
        $this->assertCount(1, $this->em->getAll());
        $this->assertEquals(['toPing'], $this->em->getAll());
    }

    public function testGet()
    {
        $this->em->add($this->toPingMock, 'toPing');
        $this->assertInstanceOf('Overwatch\ServiceBundle\Expectation\ToPingExpectation', $this->em->get('toPing'));
    }

    public function testRunSuccess()
    {
        $info = 'Pinged in 0.1s';
        $this->setUpTestRun($info);

        $result = $this->em->run($this->test);

        $this->assertInstanceOf('Overwatch\ResultBundle\Entity\TestResult', $result);
        $this->assertEquals($result->getTest(), $this->test);
        $this->assertEquals($result->getStatus(), ResultStatus::PASSED);
        $this->assertEquals($result->getInfo(), $info);
    }

    public function testRunUnsatisfactory()
    {
        $info = '8.8.8.8 responded in 0.6 s, above the unsatisfactory threshold (0.5 s)';
        $this->setUpTestRun(new ExpectationException\ExpectationUnsatisfactoryException($info));

        $result = $this->em->run($this->test);

        $this->assertInstanceOf('Overwatch\ResultBundle\Entity\TestResult', $result);
        $this->assertEquals($result->getTest(), $this->test);
        $this->assertEquals($result->getStatus(), ResultStatus::UNSATISFACTORY);
        $this->assertEquals($result->getInfo(), $info);
    }

    public function testRunFailed()
    {
        $info = '8.8.8.8 failed to respond in the timeout threshold (1 s)';
        $this->setUpTestRun(new ExpectationException\ExpectationFailedException($info));

        $result = $this->em->run($this->test);

        $this->assertInstanceOf('Overwatch\ResultBundle\Entity\TestResult', $result);
        $this->assertEquals($result->getTest(), $this->test);
        $this->assertEquals($result->getStatus(), ResultStatus::FAILED);
        $this->assertEquals($result->getInfo(), $info);
    }

    public function testRunError()
    {
        $info = 'An exception got thrown and f**k knows why';
        $this->setUpTestRun(new \Exception($info));

        $result = $this->em->run($this->test);

        $this->assertInstanceOf('Overwatch\ResultBundle\Entity\TestResult', $result);
        $this->assertEquals($result->getTest(), $this->test);
        $this->assertEquals($result->getStatus(), ResultStatus::ERROR);
        $this->assertEquals($result->getInfo(), $info);
    }
    
    public function testRunErrorThenPass()
    {
        $info = 'Passes the second!';

        $this->setUpTestRun([
            $this->throwException(new \Exception('Fails the first time')),
            $this->returnValue($info)
        ]);

        $result = $this->em->run($this->test);

        $this->assertInstanceOf('Overwatch\ResultBundle\Entity\TestResult', $result);
        $this->assertEquals($result->getTest(), $this->test);
        $this->assertEquals($result->getStatus(), ResultStatus::PASSED);
        $this->assertEquals($result->getInfo(), $info);
    }
}
