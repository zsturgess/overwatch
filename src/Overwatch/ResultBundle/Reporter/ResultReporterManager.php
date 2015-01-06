<?php

namespace Overwatch\ResultBundle\Reporter;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Reporter\ResultReporterInterface;

/**
 * ResultReporterManager
 * The ResultReporterManager keeps a list of all known ResultReporters and also
 * acts as an event listener to the doctrine postPersist event. When a postPersist
 * event is fired for the TestResult class, this Manager will notify all ResultReporters
 * that have been added to it.
 */
class ResultReporterManager {
    private $resultReporters = [];
    private $logger;
    
    public function __construct($logger) {
        $this->logger = $logger;
    }
    
    public function add(ResultReporterInterface $reporter) {
        $this->resultReporters[] = $reporter;
    }
    
    public function notifyAll(TestResult $result) {
        foreach ($this->resultReporters as $reporter) {
            try {
                $reporter->notify($result);
            } catch (\Exception $ex) {
                $this->logger->error("An error occurred whilst calling ResultReporter " . \get_class($reporter) . ":" . $ex);
            }
        }
    }
    
    public function postPersist(LifecycleEventArgs $args) {
        $entity = $args->getEntity();
        
        if ($entity instanceof TestResult) {
            $this->notifyAll($entity);
        }
    }
}
