<?php

namespace Overwatch\ExpectationBundle\Helper;

interface ExpectationInterface {
    public function run($actual, $expected = NULL);
}
