<?php

namespace Overwatch\ServiceBundle\Reporter;

use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Reporter\ResultReporterInterface;
use Overwatch\UserBundle\Entity\User;
use Services_Twilio;

/**
 * SmsReporter
 */
class SmsReporter implements ResultReporterInterface
{
    private $container;
    private $config;
    private $twilio = null;

    public function __construct($container, $config, Services_Twilio $twilio = null)
    {
        $this->container = $container;
        $this->config = $config;
        $this->twilio = $twilio;
    }
    
    public function notify(TestResult $result)
    {
        if ($this->config['enabled'] === false) {
            return;
        }
        
        if ($this->twilio === null) {
            $this->twilio = new \Services_Twilio(
                $this->config['twilio_account_sid'],
                $this->config['twilio_auth_token']
            );
        }
        
        foreach ($result->getTest()->getGroup()->getUsers() as $user) {
            if ($user->shouldBeAlerted($result)) {
                $this->sendSms($result, $user);
            }
        }
    }
    
    private function sendSms(TestResult $result, User $user)
    {
        if (empty($user->getTelephoneNumber())) {
            return;
        }
        
        $this->twilio->account->messages->sendMessage(
            $this->config['twilio_from_number'],
            $user->getTelephoneNumber(),
            $this->container->get('templating')->render(
                'OverwatchServiceBundle:Sms:result.txt.twig',
                ['result' => $result]
            )
        );
    }
}
