<?php

namespace Overwatch\ResultBundle\Enum;

/**
 * ResultStatus
 */
class ResultStatus
{
    const FAILED = 'FAILED';

    const ERROR = 'ERROR';

    const UNSATISFACTORY = 'UNSATISFACTORY';

    const PASSED = 'PASSED';

    public static function getAll()
    {
        return [
            self::FAILED,
            self::ERROR,
            self::UNSATISFACTORY,
            self::PASSED,
        ];
    }

    public static function isValid($status)
    {
        if (!in_array($status, self::getAll())) {
            throw new \InvalidArgumentException($status . ' is not a valid TestResult status');
        }
    }
}
