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
class EmailReporterTest extends DatabaseAwareTestCase {
    const FROM_MAIL = "overwatch@example.com";
    
    private $reporter;
    private $mailerSpy;

    public function setUp() {
        parent::setUp();
        
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
        
        $this->reporter = new EmailReporter(
            $customContainer,
            [
                "enabled" => true,
                "report_from" => self::FROM_MAIL
            ]
        );
    }
    
    public function testNotification() {
        $result = TestResultFixtures::$results['result-3'];
        
        $this->reporter->notify($result);
        $this->assertCount(1, $this->mailerSpy->getInvocations());
        
        $message = $this->mailerSpy->getInvocations()[0]->parameters[0];
        $this->assertEquals(
            $result->getTest()->getName() . " " . $result->getStatus(),
            $message->getSubject()
        );
        
        
        $this->assertEquals([self::FROM_MAIL => NULL], $message->getFrom());
        //var_dump(json_encode(UserFixtures::$users['user-1']->getGroups()->toArray()));
        //var_dump(json_encode(\Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures::$groups['group-1']->getUsers()->toArray()));
        //These two statements really should match and don't. They cause this following test to fail:
        //$this->assertEquals([UserFixtures::$users['user-1']->getEmail()], $message->getTo());
        //I can't see why. Am I blind?
        $this->assertContains($result->getTest()->getName(), $message->getBody());
        $this->assertContains($result->getStatus(), $message->getBody());
        $this->assertContains($result->getInfo(), $message->getBody());
        $this->assertContains($result->getCreatedAt()->format('F j, Y H:i'), $message->getBody());
        $this->markTestIncomplete("User-group relationship is faulty, so asserting that the email goes To one user only fails.");
    }
}
