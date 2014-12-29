<?php

namespace Overwatch\ExpectationBundle\Exception;

/**
 * ExpectationFailedException
 * The exception an Expectation class is expected to throw if the expectation has failed.
 * Expectation classes MUST NOT throw this exception in response to unexpected exceptions,
 * or an unsatisfactory result, other exceptions will be caught and handled as errors
 * correctly by the ExpectionManager without further processing.
 */
class ExpectationFailedException extends ExpectationResultException {}
