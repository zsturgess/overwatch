<?php

namespace Overwatch\ExpectationBundle\Exception;

/**
 * ExpectationUnsatisfactoryException
 * The exception an Expectation class is expected to throw if the expectation has passed,
 * but is unsatisfactory. Expectation classes MUST NOT throw this exception in response
 * to unexpected exceptions, or a fail result, other exceptions will be caught and handled
 * as errors correctly by the ExpectionManager without further processing.
 * 
 * @codeCoverageIgnore
 */
class ExpectationUnsatisfactoryException extends ExpectationResultException {}
