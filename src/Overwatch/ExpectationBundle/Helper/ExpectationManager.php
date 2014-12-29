<?php

namespace Overwatch\ExpectationBundle\Helper;

use Overwatch\ExpectationBundle\Exception\ExpectationNotFoundException;

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
            throw new ExpectationNotFoundException($alias);
        }
        
        return $this->expectations[$alias];
    }
    
    public function getAll() {
        return array_keys($this->expectations);
    }
    
    public function run($actual, $alias, $expected = NULL) {
        try {
            return $this->get($alias)->run($actual, $expected);
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}
