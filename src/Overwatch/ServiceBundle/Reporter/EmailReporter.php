<?php

namespace Overwatch\ServiceBundle\Reporter;

use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Reporter\ResultReporterInterface;

/**
 * EmailReporter
 */
class EmailReporter implements ResultReporterInterface  {
    private $mailer;
    private $templating;
    private $config;
    
    public function __construct($mailer, $templating, $config) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->config = $config;
    }
    
    public function notify(TestResult $result) {
        if ($this->config["enabled"] === false) {
            return;
        }
        
        $receipients = [];
        
        foreach ($result->getTest()->getGroup()->getUsers() as $user) {
            if ($user->shouldBeAlerted($result)) {
                $receipients[] = $user->getEmail();
            }
        }
        
        $this->sendEmail($result, $receipients);
    }
    
    private function sendEmail(TestResult $result, array $users) {
        $message = \Swift_Message::newInstance()
            ->setSubject($result->getTest()->getName() . " " . $result->getStatus())
            ->setFrom($this->config['report_from'])
            ->setTo($users)
            ->setBody(
                $this->templating->render(
                    'OverwatchServiceBundle:Email:result.txt.twig',
                    ["result" => $result]
                ),
                'text\plain'
            )
        ;
        
        $this->mailer->send($message);
    }
}
