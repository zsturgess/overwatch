<?php

namespace Overwatch\TestBundle\Tests\Command;

use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;

/**
 * ConsoleTestHelperTrait
 */
trait ConsoleTestHelperTrait {
    protected $output = null;
    
    public function assertHasStandardOutput() {
        $this->assertStringStartsWith($this->application->getName(), $this->output[0]);
        $this->assertContains($this->application->getVersion(), $this->output[0]);
        
        foreach ($this->output as $lineNum => $line) {
            if ($lineNum === 0 || $lineNum === count($this->output) - 1) {
                continue;
            }
            
            $this->assertStringStartsWith(' > ', $line);
        }
    }
    
    /**
     * @param integer $count
     */
    public function assertCountLinesOfOutput($count) {
        $this->assertCount($count, $this->output);
    }
    
    /**
     * @param integer $count
     */
    public function assertCountRunTests($count = null) {
        if ($count === null) {
            $count = count(TestFixtures::$tests);
        }
        
        $this->assertStringEndsWith("$count tests", $this->output[0]);
    }
    
    public function assertResults($failed = '[0-9]+', $error = '[0-9]+', $unsatisfactory = '[0-9]+', $passed = '[0-9]+') {
        $this->assertRegExp(
            "/^$failed FAILED, $error ERROR, $unsatisfactory UNSATISFACTORY, $passed PASSED/",
            $this->output[count($this->output) - 1]
        );
        $this->assertRegExp('/, in [0-9]+ minutes and [0-9]+ seconds$/i', $this->output[count($this->output) - 1]);
    }
    
    public function assertRecentResultsPersisted($count = 3) {
        $now = (new \DateTime())->getTimestamp();
        $results = $this->resultRepo->getResults([], $count);
        
        foreach ($results as $result) {
            $diff = $now - $result->getCreatedAt()->getTimestamp();
            
            $this->assertLessThan(5, $diff);
        }
    }
    
    public function assertRecentResultsNotPersisted() {
        $result = $this->resultRepo->getResults([], 1);
        
        $this->assertGreaterThan(500, $result[0]->getCreatedAt()->getTimestamp());
    }
    
    protected function execute($params = [], $options = []) {
        $params['command'] = self::COMMAND_NAME;
        $returnCode = $this->command->execute($params, $options);
        
        $this->output = explode(PHP_EOL, $this->command->getDisplay());
        $lastLine = array_pop($this->output);
        
        if (!empty($lastLine)) {
            array_push($this->output, $lastLine);
        }
        
        return $returnCode;
    }
}
