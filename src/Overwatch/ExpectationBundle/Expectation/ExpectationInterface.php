<?php

namespace Overwatch\ExpectationBundle\Expectation;

/**
 * ExpectationInterfaces
 * Expectation classes are expected to implement this interface.
 * The code to run a test goes inside the run() function.
 */
interface ExpectationInterface {
	/**
	 * Attempt to execute the expectation, resulting in a textual result if successful, or throwing
	 * an exception if the expectation failed.
	 *
	 * @param  string $actual
	 * @param  string $expected
	 * @return string
	 * @throws ExpectationFailedException
	 * @throws ExpectationNotFoundException
	 * @throws ExpectationResultException
	 * @throws ExpectationUnsatisfactoryException
	 */
    public function run($actual, $expected = NULL);
}
