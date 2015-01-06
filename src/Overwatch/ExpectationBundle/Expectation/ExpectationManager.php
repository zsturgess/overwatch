<?php

namespace Overwatch\ExpectationBundle\Expectation;

use Overwatch\ExpectationBundle\Exception as ExpectationException;
use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Enum\ResultStatus;

/**
 * ExpectationManager
 * The ExpectationManager keeps a list of all known expectations and provides
 * a run() convience method that finds an expectation by alias and passes through
 * the actual and expected values to it for testing.
 */
class ExpectationManager {
    private $expectations = array();
    
    public function add(ExpectationInterface $expectation, $alias) {
        $this->expectations[$alias] = $expectation;
    }
    
    public function get($alias) {
        if (!array_key_exists($alias, $this->expectations)) {
            throw new ExpectationException\ExpectationNotFoundException($alias);
        }
        
        return $this->expectations[$alias];
    }
    
    public function getAll() {
        return array_keys($this->expectations);
    }
    
    public function run(\Overwatch\TestBundle\Entity\Test $test) {
        $testResult = new TestResult;
        $testResult->setTest($test);
        
        try {
            $result = $this->get($test->getExpectation())->run($test->getActual(), $test->getExpected());
            $testResult->setStatus(ResultStatus::PASSED);
            $testResult->setInfo($result);
        } catch (\Exception $ex) {
            $result = $ex;
            $testResult->setInfo($ex->getMessage());
        }
        
        if ($result instanceof ExpectationException\ExpectationFailedException) {
            $testResult->setStatus(ResultStatus::FAILED);
        } else if ($result instanceof ExpectationException\ExpectationUnsatisfactoryException) {
            $testResult->setStatus(ResultStatus::UNSATISFACTORY);
        } else if ($result instanceof \Exception) {
            $testResult->setStatus(ResultStatus::ERROR);
        }
        
        return $testResult;
    }
}
