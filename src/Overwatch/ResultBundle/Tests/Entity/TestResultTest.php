<?php

namespace Overwatch\ResultBundle\Tests\Entity;

use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Enum\ResultStatus;
use Overwatch\TestBundle\Entity\Test;

/**
 * TestResultTest
 * A unit test for the TestResult Entity
 */
class TestResultTest extends \PHPUnit_Framework_TestCase
{
    private $test;

    private $result;

    public function setUp()
    {
        $this->test = new Test;
        $this->test
            ->setName('A test for testing')
        ;

        $this->result = new TestResult();
    }

    public function testValid()
    {
        $status = ResultStatus::PASSED;
        $info = 'Bacon ipsum dolor amet kielbasa beef ribs beef venison.';

        $this->result
            ->setTest($this->test)
            ->setStatus($status)
            ->setInfo($info)
            ->setCreatedAt();

        $this->assertEquals($this->test, $this->result->getTest());
        $this->assertEquals($status, $this->result->getStatus());
        $this->assertEquals($info, $this->result->getInfo());
        $this->assertInstanceOf('\DateTime', $this->result->getCreatedAt());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'id' => NULL,
                'status' => $status,
                'info' => $info,
                'createdAt' => $this->result->getCreatedAt()->getTimestamp(),
            ]),
            json_encode($this->result)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidStatus()
    {
        $this->result->setStatus("IfThisStatusExistsIBrokeIt");
    }

    public function testCreatedAtIsImmutable()
    {
        $this->result->setCreatedAt();
        $expected = $this->result->getCreatedAt();

        $this->result->setCreatedAt();
        $this->assertEquals($expected, $this->result->getCreatedAt());
    }

    public function testSetInfoConvertsExceptions()
    {
        $message = "Bacon ipsum dolor amet fatback nostrud beef venison mollit officia.";
        $this->result->setInfo(new \Exception($message));

        $this->assertNotInstanceOf('\Exception', $this->result->getInfo());
        $this->assertEquals($message, $this->result->getInfo());
    }

    public function testIsAChange()
    {
        $oldResult = new TestResult();
        $oldResult
            ->setStatus(ResultStatus::PASSED)
            ->setTest($this->test);
        $this->test->addResult($oldResult);

        $this->result
            ->setStatus(ResultStatus::PASSED)
            ->setTest($this->test);
        $this->test->addResult($this->result);

        $this->assertFalse($this->result->isAChange());

        $newResult = new TestResult();
        $newResult
            ->setStatus(ResultStatus::UNSATISFACTORY)
            ->setTest($this->test);
        $this->test->addResult($newResult);

        $this->assertTrue($newResult->isAChange());
    }

    public function testIsAChangeNoTest()
    {
        $this->assertTrue($this->result->isAChange());
    }

    public function testIsAChangeFirstResult()
    {
        $this->result
            ->setTest($this->test);

        $this->assertTrue($this->result->isAChange());
    }
}
