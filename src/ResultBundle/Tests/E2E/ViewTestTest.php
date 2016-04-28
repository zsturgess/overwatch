<?php

namespace Overwatch\ResultBundle\Tests\E2E;

use Facebook\WebDriver\WebDriverBy;
use Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures;
use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;
use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;

/**
 * ViewTestTest
 * Tests the View Test page
 */
class ViewTestTest extends WebDriverTestCase
{
    public function testTest1Results()
    {
        $this->logInAsUser('user-1');
        $this->clickThroughToTest(1);

        $this->waitForLoadingAnimation();
        $this->assertContains(TestFixtures::$tests['test-1']->getName(), $this->getHeaderText());
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

    public function testTest2Results()
    {
        $this->logInAsUser('user-1');
        $this->clickThroughToTest(2);

        $this->waitForLoadingAnimation();
        $this->assertContains(TestFixtures::$tests['test-2']->getName(), $this->getHeaderText());
        $this->assertCount(1, $this->getResultsOnPage());
        $this->assertEquals(TestResultFixtures::$results['result-4']->getInfo(), $this->getResultsOnPage()[0]->getText());
        $this->assertTimestampEquals(TestResultFixtures::$results['result-4']->getCreatedAt(), $this->getResultsOnPage(' a')[0]->getAttribute('title'));
        $this->assertContains(strtolower(TestResultFixtures::$results['result-4']->getStatus()), $this->getResultsOnPage('')[0]->getAttribute('class'));
    }

    public function testDeleteTest()
    {
        $this->logInAsUser('user-1');
        $this->clickThroughToTest(1);

        $this->waitForLoadingAnimation();
        $this->getActionItem(2)->click();

        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->accept();

        $this->waitForLoadingAnimation();
        $this->assertCount(1, $this->webDriver->findElements(
            WebDriverBy::cssSelector('.groups .widget-box:first-child .tests li div.test')
        ));
    }

    public function testRunTest()
    {
        $this->logInAsUser('user-1');
        $this->clickThroughToTest(1);

        $this->waitForLoadingAnimation();
        $this->getActionItem(3)->click();
        $this->waitForLoadingAnimation();
        $this->assertCount(4, $this->getResultsOnPage());
    }

    public function testEditDeleteAndRunInsufficentPermissions()
    {
        $this->logInAsUser('user-2');
        $this->clickThroughToTest(1);

        $this->assertFalse($this->getActionItem(1)->isDisplayed());
        $this->assertFalse($this->getActionItem(2)->isDisplayed());
        $this->assertFalse($this->getActionItem(3)->isDisplayed());
    }

    private function clickThroughToTest($number)
    {
        $this->waitForLoadingAnimation();
        $this->webDriver->findElement(
            WebDriverBy::cssSelector('.tests li:nth-child(' . $number . ') .test a:nth-child(2)')
        )->click();
    }

    private function getResultsOnPage($selector = ' span')
    {
        $selector = '.result' . $selector;

        $results = $this->webDriver->findElements(
            WebDriverBy::cssSelector($selector)
        );

        return $results;
    }

    private function getActionItem($number)
    {
        return $this->webDriver->findElement(
            WebDriverBy::cssSelector('.widget-content .row a:nth-child(' . $number . ')')
        );
    }

    private function assertTimestampEquals($expected, $actual)
    {
        if (!$expected instanceof \DateTime) {
            $expected = new \DateTime($expected);
        }

        if (!$actual instanceof \DateTime) {
            $actual = new \DateTime($actual);
        }

        $this->assertEquals($expected, $actual);
    }
}
