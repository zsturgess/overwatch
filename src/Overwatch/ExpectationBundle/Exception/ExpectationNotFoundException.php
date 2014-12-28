<?php

namespace Overwatch\ExpectationBundle\Exception;

class ExpectationNotFoundException extends \OutOfBoundsException {
    public function __construct($alias, $code, $previous) {
        parent::__construct(
            "Expectation with $alias could not be found.",
            $code,
            $previous
        );
    }
}