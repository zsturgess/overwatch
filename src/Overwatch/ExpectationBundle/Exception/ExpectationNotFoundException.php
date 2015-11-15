<?php

namespace Overwatch\ExpectationBundle\Exception;

/**
 * ExpectationNotFoundException
 * Thrown by the ExpectationManager when requested to find a non-existant
 * expectation by alias.
 */
class ExpectationNotFoundException extends \OutOfBoundsException
{
    public function __construct($alias, $code = 0, $previous = null)
    {
        parent::__construct(
            "Expectation with $alias could not be found.",
            $code,
            $previous
        );
    }
}
