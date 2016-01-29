<?php

namespace Overwatch\ServiceBundle\Tests\Reporter;

use Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures;
use Overwatch\ServiceBundle\Reporter\EmailReporter;
use Overwatch\UserBundle\DataFixtures\ORM\UserFixtures;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;

/**
 * EmailReporterTest
 * Functional Test for the operation of the EmailReporter
 */
class EmailReporterTest extends DatabaseAwareTestCase
{
    const FROM_MAIL = 'overwatch@example.com';
    
    private $reporter;
    private $mailerSpy;

    public function setUp()
    {
        parent::setUp();
        
        $this->reporter = new EmailReporter(
            $this->getMockedEnvironment(),
            [
                'enabled'     => true,
                'report_from' => self::FROM_MAIL
            ]
        );
    }
    
    public function testNotification()
    {
        $result = $this->em->find("Overwatch\ResultBundle\Entity\TestResult", TestResultFixtures::$results['result-3']->getId());
        
        $this->reporter->notify($result);
        $this->assertCount(1, $this->mailerSpy->getInvocations());
        
        $message = $this->mailerSpy->getInvocations()[0]->parameters[0];
        $this->assertEquals(
            $result->getTest()->getName() . ' ' . $result->getStatus(),
            $message->getSubject()
        );
        
        
        $this->assertEquals([self::FROM_MAIL => null], $message->getFrom());
        $this->assertEquals([UserFixtures::$users['user-1']->getEmail() => null], $message->getTo());
        $this->assertContains($result->getTest()->getName(), $message->getBody());
        $this->assertContains($result->getStatus(), $message->getBody());
        $this->assertContains($result->getInfo(), $message->getBody());
        $this->assertContains($result->getCreatedAt()->format('F j, Y H:i'), $message->getBody());
    }
    
    public function testDisabled()
    {
        $reporter = new EmailReporter(
            $this->getMockedEnvironment(),
            [
                'enabled'     => false,
                'report_from' => self::FROM_MAIL
            ]
        );
        
        $result = $this->em->find("Overwatch\ResultBundle\Entity\TestResult", TestResultFixtures::$results['result-3']->getId());
        
        $reporter->notify($result);
        $this->assertCount(0, $this->mailerSpy->getInvocations());
    }
    
    private function getMockedEnvironment()
    {
        $mailer = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();
        $mailer
            ->expects($this->mailerSpy = $this->any())
            ->method('send')
            ->willReturn(1)
        ;
                
        $customContainer = $this->getContainer();
        $customContainer->set('swiftmailer.mailer.default', $mailer);
        
        return $customContainer;
    }
}
