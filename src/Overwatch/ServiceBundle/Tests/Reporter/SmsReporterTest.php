<?php

namespace Overwatch\ServiceBundle\Tests\Reporter;

use Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures;
use Overwatch\ServiceBundle\Reporter\SmsReporter;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;
use Overwatch\UserBundle\DataFixtures\ORM\UserFixtures;

/**
 * SmsReporterTest
 */
class SmsReporterTest extends DatabaseAwareTestCase {
    const FROM_TEL = '+CCXXXXXXXXXX';
    
    private $reporter;
    private $messageSpy;
    
    public function setUp()
    {
        parent::setUp();

        $twilio = $this->getMockBuilder('Services_Twilio')
            ->disableOriginalConstructor()
            ->getMock();

        $twilio->account = $twilio;

        $messagesMock = $this->getMockBuilder('Services_Twilio_Rest_Messages')
            ->disableOriginalConstructor()
            ->getMock();
        $messagesMock->expects($this->messageSpy = $this->any())
            ->method('sendMessage')
            ->willReturn(true);

        $twilio->messages = $messagesMock;

        $this->reporter = new SmsReporter(
            $this->getContainer(),
            $this->getTwilioConfig(),
            $twilio
        );
    }
    
    public function testNotification()
    {
        $result = $this->em->find("Overwatch\ResultBundle\Entity\TestResult", TestResultFixtures::$results['result-3']->getId());
        
        $this->reporter->notify($result);
        $this->assertCount(1, $this->messageSpy->getInvocations());
        
        $message = $this->messageSpy->getInvocations()[0]->parameters[2];
        $this->assertEquals(
            $result->getTest()->getName() . " " . $result->getStatus() . ': Me gusta success kid upvoting Obama first world problems.',
            $message
        );
        
        
        $this->assertEquals(self::FROM_TEL, $this->messageSpy->getInvocations()[0]->parameters[0]);
        $this->assertEquals(UserFixtures::$users['user-1']->getTelephoneNumber(), $this->messageSpy->getInvocations()[0]->parameters[1]);
    }
    
    public function testDisabled() {
        $reporter = new SmsReporter(
            $this->getContainer(),
            $this->getTwilioConfig(false)
        );
        
        $result = $this->em->find("Overwatch\ResultBundle\Entity\TestResult", TestResultFixtures::$results['result-3']->getId());
        
        $reporter->notify($result);
        $this->assertCount(0, $this->messageSpy->getInvocations());
    }
    
    private function getTwilioConfig($enabled = true) {
        return [
            "enabled" => $enabled,
            'twilio_account_sid' => 'ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            'twilio_auth_token' => 'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY',
            'twilio_from_number' => self::FROM_TEL
        ];
    }
}
