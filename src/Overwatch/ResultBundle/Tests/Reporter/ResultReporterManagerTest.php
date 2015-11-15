<?php

namespace Overwatch\ResultBundle\Tests\Reporter;

use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Reporter\ResultReporterManager;

/**
 * ResultReporterManagerTest
 * Unit test of ResultReporterManager
 */
class ResultReporterManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $rrm;

    protected $loggerSpy;

    protected $emailReporterMock;

    public function setUp()
    {
        $loggerMock = $this->getMockBuilder('Symfony\Bridge\Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock
            ->expects($this->loggerSpy = $this->any())
            ->method('error')
            ->willReturn(null)
        ;

        $this->emailReporterMock = $this->getMockBuilder('Overwatch\ServiceBundle\Reporter\EmailReporter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->rrm = new ResultReporterManager($loggerMock);
    }

    public function testAdd()
    {
        $this->emailReporterMock
            ->expects($notifySpy = $this->any())
            ->method('notify')
            ->willReturn(null)
        ;

        $this->rrm->add($this->emailReporterMock);
        $this->rrm->notifyAll(new TestResult);

        $this->assertCount(1, $notifySpy->getInvocations());
    }

    public function testNotifyLogsExceptions()
    {
        $this->emailReporterMock
            ->expects($this->any())
            ->method('notify')
            ->willThrowException(new \Exception("Beard error #4"))
        ;

        $this->rrm->add($this->emailReporterMock);
        $this->rrm->notifyAll(new TestResult);

        $this->assertCount(1, $this->loggerSpy->getInvocations());
    }
}
