<?php

namespace Overwatch\ResultBundle\Reporter;

use Overwatch\ResultBundle\Entity\TestResult;

/**
 * ResultReporterInterface
 * ResultReporter classes are expected to implement this interface.
 * Code to notify users should go inside the notify() function.
 */
interface ResultReporterInterface {
    public function notify(TestResult $result);
}
