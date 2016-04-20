<?php

namespace Overwatch\TestBundle\Tests\E2E;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverSelect;
use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;
use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;

/**
 * AddEditTestTest
 * Tests the add/edit group view
 */
class AddEditTestTest extends WebDriverTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
    }

    public function testEditTest()
    {
        $this->webDriver->findElement(
            //View first test
            WebDriverBy::cssSelector('.groups .widget-box:nth-child(1) .tests li:nth-child(1) .buttons a:nth-child(3)')
        )->click();
        $this->waitForLoadingAnimation();

        $this->webDriver->findElement(
            //Edit test
            WebDriverBy::cssSelector('ul.results li:last-child a')
        )->click();
        $this->waitForLoadingAnimation();

        $this->checkTestField('name');
        $this->checkTestField('actual');
        $this->checkTestField('expectation');
        $this->checkTestField('expected');

        $this->getTestField('name')->clear();
        $this->getTestField('name')->sendKeys('UnUnTestium');
        $this->webDriver->findElement(
            WebDriverBy::cssSelector("button[data-ng-click='save()']")
        )->click();
        $this->waitForLoadingAnimation();
        $this->assertEquals('UnUnTestium', $this->getHeaderText());
    }

    public function testAddTest()
    {
        $this->webDriver->findElement(
            //Add test button
            WebDriverBy::cssSelector('ul.tests li:last-child a')
        )->click();

        $this->getTestField('name')->sendKeys('Github Status Resolves');
        $this->getTestField('actual')->sendKeys('status.github.com');
        (new WebDriverSelect($this->getTestField('expectation')))->selectByValue('string:toResolveTo');
        $this->getTestField('expected')->sendKeys('octostatus-production.github.com');
        $this->getTestField('expected')->sendKeys(WebDriverKeys::ENTER);

        $this->waitForLoadingAnimation();
        $this->assertCount(3, $this->getTestsForFirstGroup());
        $this->assertContains('Github Status Resolves', $this->getTestsForFirstGroup()[2]->getText());
    }

    private function checkTestField($field, $value = null)
    {
        $field = strtolower($field);

        if ($value === null) {
            $value = TestFixtures::$tests['test-1']->{'get' . ucfirst($field)}();
        }

        if ($field === 'expectation') {
            $select = new WebDriverSelect(
                $this->getTestField('expectation')
            );

            $this->assertEquals($value, $select->getFirstSelectedOption()->getText());
            return;
        }

        $this->assertEquals($value, $this->getTestField($field)->getAttribute('value'));
    }

    private function getTestField($field)
    {
        return $this->webDriver->findElement(
            WebDriverBy::cssSelector("*[data-ng-model='test.$field']")
        );
    }

    private function getTestsForFirstGroup()
    {
        $groups = $this->webDriver->findElements(
            WebDriverBy::cssSelector('.groups > li.ng-scope')
        );

        if (count($groups) < 1) {
            return [];
        }

        return $groups[0]->findElements(
            WebDriverBy::cssSelector('.tests li.ng-scope')
        );
    }
}
