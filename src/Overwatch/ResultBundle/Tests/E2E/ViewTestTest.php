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
        $this->assertTimestampEquals(TestResultFixtures::$results['result-3']->getCreatedAt(), $this->getResultsOnPage(' a')[0]->getAttribute('title'));
        $this->assertContains(strtolower(TestResultFixtures::$results['result-3']->getStatus()), $this->getResultsOnPage('')[0]->getAttribute('class'));
        $this->assertEquals(TestResultFixtures::$results['result-2']->getInfo(), $this->getResultsOnPage()[1]->getText());
        $this->assertTimestampEquals(TestResultFixtures::$results['result-2']->getCreatedAt(), $this->getResultsOnPage(' a')[1]->getAttribute('title'));
        $this->assertContains(strtolower(TestResultFixtures::$results['result-2']->getStatus()), $this->getResultsOnPage('')[1]->getAttribute('class'));
        $this->assertEquals(TestResultFixtures::$results['result-1']->getInfo(), $this->getResultsOnPage()[2]->getText());
        $this->assertTimestampEquals(TestResultFixtures::$results['result-1']->getCreatedAt(), $this->getResultsOnPage(' a')[2]->getAttribute('title'));
        $this->assertContains(strtolower(TestResultFixtures::$results['result-1']->getStatus()), $this->getResultsOnPage('')[2]->getAttribute('class'));
    }
    
    public function testTest2Results() {
        $this->logInAsUser('user-1');
        $this->clickThroughToTest(2);
        
        $this->waitForLoadingAnimation();
        $this->assertEquals(TestFixtures::$tests['test-2']->getName(), $this->getHeaderText());
        $this->assertCount(1, $this->getResultsOnPage());
        $this->assertEquals(TestResultFixtures::$results['result-4']->getInfo(), $this->getResultsOnPage()[0]->getText());
        $this->assertTimestampEquals(TestResultFixtures::$results['result-4']->getCreatedAt(), $this->getResultsOnPage(' a')[0]->getAttribute('title'));
        $this->assertContains(strtolower(TestResultFixtures::$results['result-4']->getStatus()), $this->getResultsOnPage('')[0]->getAttribute('class'));
    }
    
    private function clickThroughToTest($number) {
        $this->waitForLoadingAnimation();
        $this->webDriver->findElement(
            \WebDriverBy::cssSelector('.tests li:nth-child(' . $number . ') .test a:nth-child(3)')
        )->click();
    }
    
    private function getResultsOnPage($selector = ' span') {
        $selector = '.result' . $selector;
        
        $results = $this->webDriver->findElements(
            \WebDriverBy::cssSelector($selector)
        );
        
        return $results;
    }
    
    private function assertTimestampEquals($expected, $actual) {
        $this->assertEquals(new \DateTime($expected), new \DateTime($actual));
    }
}
