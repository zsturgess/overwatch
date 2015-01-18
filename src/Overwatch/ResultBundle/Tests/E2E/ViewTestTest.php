<?php

namespace Overwatch\ResultBundle\Tests\E2E;

use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;
use Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures;
use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;

/**
 * ViewTestTest
 * Tests the View Test page
 */
class ViewTestTest extends WebDriverTestCase {
    public function testTest1Results() {
        $this->logInAsUser('user-1');
        $this->clickThroughToTest(1);
        
        $this->waitForLoadingAnimation();
        $this->assertEquals(TestFixtures::$tests['test-1']->getName(), $this->getHeaderText());
        $this->assertCount(3, $this->getResultsOnPage());
        $this->assertEquals(TestResultFixtures::$results['result-3']->getInfo(), $this->getResultsOnPage()[0]->getText());
        $this->assertContains(strtolower(TestResultFixtures::$results['result-3']->getStatus()), $this->getResultsOnPage(FALSE)[0]->getAttribute('class'));
        $this->assertEquals(TestResultFixtures::$results['result-2']->getInfo(), $this->getResultsOnPage()[1]->getText());
        $this->assertContains(strtolower(TestResultFixtures::$results['result-2']->getStatus()), $this->getResultsOnPage(FALSE)[1]->getAttribute('class'));
        $this->assertEquals(TestResultFixtures::$results['result-1']->getInfo(), $this->getResultsOnPage()[2]->getText());
        $this->assertContains(strtolower(TestResultFixtures::$results['result-1']->getStatus()), $this->getResultsOnPage(FALSE)[2]->getAttribute('class'));
    }
    
    public function testTest2Results() {
        $this->logInAsUser('user-1');
        $this->clickThroughToTest(2);
        
        $this->waitForLoadingAnimation();
        $this->assertEquals(TestFixtures::$tests['test-2']->getName(), $this->getHeaderText());
        $this->assertCount(1, $this->getResultsOnPage());
        $this->assertEquals(TestResultFixtures::$results['result-4']->getInfo(), $this->getResultsOnPage()[0]->getText());
        $this->assertContains(strtolower(TestResultFixtures::$results['result-4']->getStatus()), $this->getResultsOnPage(FALSE)[0]->getAttribute('class'));
    }
    
    private function clickThroughToTest($number) {
        $this->waitForLoadingAnimation();
        $this->webDriver->findElement(
            \WebDriverBy::cssSelector('.tests li:nth-child(' . $number . ') .test a:nth-child(3)')
        )->click();
    }
    
    private function getResultsOnPage($getSpan = TRUE) {
        $selector = '.result';
        if ($getSpan) {
            $selector .= ' span';
        }
        
        $results = $this->webDriver->findElements(
            \WebDriverBy::cssSelector($selector)
        );
        
        return $results;
    }
}
