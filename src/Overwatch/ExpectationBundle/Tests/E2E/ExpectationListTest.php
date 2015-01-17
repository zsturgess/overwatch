<?php

namespace Overwatch\ExpectationBundle\Tests\E2E;

use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;

/**
 * ExpectationListTest
 * Tests that the frontend drop-down matches the list of expectations
 */
class ExpectationListTest extends WebDriverTestCase {
    public function testExpectationListPopulates() {
        $this->logInAsUser('user-1');
        
        $this->waitForLoadingAnimation();
        $this->clickFirstGroupAddTestButton();
        
        $this->waitForLoadingAnimation();
        $this->assertEquals([
            "",
            "toPing",
            "toResolveTo"
        ], $this->getSelectOptionValues('#page > div > form > select'));
    }
    
    private function clickFirstGroupAddTestButton() {
        $this->webDriver->findElement(
            \WebDriverBy::cssSelector('.groups > li:nth-child(1) > ul:nth-child(2) > li:nth-child(3) > div:nth-child(1) > a:nth-child(2)')
        )->click();
    }
    
    private function getSelectOptionValues($select) {
        $select = new \WebDriverSelect($this->webDriver->findElement(
            \WebDriverBy::cssSelector($select)
        ));
        
        $options = [];
        
        foreach ($select->getOptions() as $option) {
            $options[] = $option->getText();
        }
        
        return $options;
    }
}