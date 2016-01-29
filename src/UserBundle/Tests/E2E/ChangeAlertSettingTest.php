<?php

namespace Overwatch\UserBundle\Tests\E2E;

use Facebook\WebDriver\WebDriverBy;
use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;

/**
 * ChangeAlertSettingTest
 * Tests Change Alert Settings Screen
 */
class ChangeAlertSettingTest extends WebDriverTestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->webDriver->findElement(
            WebDriverBy::cssSelector('#sidebar li:nth-child(3) a')
        )->click();
        $this->waitForLoadingAnimation();
    }
    
    public function testChangeSetting()
    {
        $this->assertFalse($this->getSettingLink(5)->isDisplayed());
        $this->assertTrue($this->getSettingLink(5, true)->isDisplayed());
        
        $this->getSettingLink(1)->click();
        $this->waitForLoadingAnimation();
        $this->assertTrue($this->getSettingLink(5)->isDisplayed());
        $this->assertFalse($this->getSettingLink(5, true)->isDisplayed());
        $this->assertFalse($this->getSettingLink(1)->isDisplayed());
        $this->assertTrue($this->getSettingLink(1, true)->isDisplayed());
    }
    
    private function getSettingLink($number, $isCurrent = false)
    {
        $selector = '[data-ng-click]';
        
        if ($isCurrent) {
            $selector = ":not($selector)";
        }
        
        return $this->getSettingRow($number)->findElement(
            WebDriverBy::cssSelector("a$selector")
        );
    }
    
    private function getSettingRow($number)
    {
        return $this->webDriver->findElement(
            WebDriverBy::cssSelector(".settings li:nth-child($number)")
        );
    }
}
