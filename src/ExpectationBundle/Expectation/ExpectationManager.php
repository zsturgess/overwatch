<?php

namespace Overwatch\ExpectationBundle\Expectation;

use Overwatch\ExpectationBundle\Exception as ExpectationException;
use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Enum\ResultStatus;
use Overwatch\TestBundle\Entity\Test;

/**
 * ExpectationManager
 * The ExpectationManager keeps a list of all known expectations and provides
 * a run() convienience method for testing.
 */
class ExpectationManager
{
    private $expectations = [];

    /**
     * Register an expectation
     * 
     * @param ExpectationInterface $expectation
     * @param string $alias
     */
    public function add(ExpectationInterface $expectation, $alias)
    {
        $this->expectations[$alias] = $expectation;
    }

    /**
     * Get a registered expectation by alias
     * 
     * @param string $alias
     * @return ExpectationInterface
     * @throws ExpectationException\ExpectationNotFoundException
     */
    public function get($alias)
    {
        if (!array_key_exists($alias, $this->expectations)) {
            throw new ExpectationException\ExpectationNotFoundException($alias);
        }

        return $this->expectations[$alias];
    }

    /**
     * Get a list of all expecations currently registered.
     * 
     * @return array
     */
    public function getAll()
    {
        return array_keys($this->expectations);
    }

    /**
     * A run() convienience method that:
     *  - Takes a test
     *  - Finds the correct expectation
     *  - Passes through the expected and actual values from the test
     *  - Constructs a TestResult based on captured output/thrown exceptions
     * 
     * @param Test $test
     * @return TestResult
     */
    public function run(Test $test)
    {
        $testResult = new TestResult();
        $testResult->setTest($test);

        // If the first run of the test is not a pass, run once more to check for false positives
        $i = 0;
        $result = '';

        while ($i < 2) {
            try {
                $result = $this->get($test->getExpectation())->run($test->getActual(), $test->getExpected());
                $testResult->setStatus(ResultStatus::PASSED);
                $testResult->setInfo($result);

                break;
            } catch (\Exception $ex) {
                $result = $ex;
                $testResult->setInfo($ex->getMessage());
            }

            $i++;
        }

        if ($result instanceof ExpectationException\ExpectationFailedException) {
            $testResult->setStatus(ResultStatus::FAILED);
        } elseif ($result instanceof ExpectationException\ExpectationUnsatisfactoryException) {
            $testResult->setStatus(ResultStatus::UNSATISFACTORY);
        } elseif ($result instanceof \Exception) {
            $testResult->setStatus(ResultStatus::ERROR);
        }

        return $testResult;
    }
}
