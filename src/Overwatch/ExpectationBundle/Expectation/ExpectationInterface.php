<?php

namespace Overwatch\ExpectationBundle\Expectation;

/**
 * ExpectationInterfaces
 * Expectation classes are expected to implement this interface.
 * The code to run a test goes inside the run() function.
 */
interface ExpectationInterface {
    public function run($actual, $expected = NULL);
}
