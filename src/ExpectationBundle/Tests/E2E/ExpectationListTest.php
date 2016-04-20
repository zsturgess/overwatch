<?php

namespace Overwatch\ExpectationBundle\Tests\E2E;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;

/**
 * ExpectationListTest
 * Tests that the frontend drop-down matches the list of expectations
 */
class ExpectationListTest extends WebDriverTestCase
{
    public function testExpectationListPopulates()
    {
        $this->logInAsUser('user-1');
        $this->clickFirstGroupAddTestButton();

        $this->waitForLoadingAnimation();
        $this->assertEquals([
            '',
            'toPing',
            'toResolveTo',
            'toRespondHttp',
            'toRespondWithMimeType',
            'toContainText',
        ], $this->getSelectOptionValues('#page > div > form > select'));
    }

    private function clickFirstGroupAddTestButton()
    {
        $this->waitForLoadingAnimation();
        $this->webDriver->findElement(
            WebDriverBy::cssSelector('.groups .widget-box:first-child .tests li:last-child a')
        )->click();
    }

    private function getSelectOptionValues($select)
    {
        $select = new WebDriverSelect($this->webDriver->findElement(
            WebDriverBy::cssSelector($select)
        ));

        $options = [];

        foreach ($select->getOptions() as $option) {
            $options[] = $option->getText();
        }

        return $options;
    }
}
