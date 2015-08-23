<?php

namespace Overwatch\UserBundle\Tests\E2E;

use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;
use Overwatch\UserBundle\DataFixtures\ORM\UserFixtures;

/**
 * MyAccountTest
 *
 * @author Zac Sturgess <zac.sturgess@wearetwogether.com>
 */
class MyAccountTest extends WebDriverTestCase {
    public function setUp() {
        parent::setUp();
        
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->webDriver->findElement(
            \WebDriverBy::cssSelector("#sidebar li:nth-child(4) a")
        )->click();
        $this->waitForLoadingAnimation();
    }
    
    public function testApiKeyHidden() {
        $this->assertEquals(
            "password",
            $this->getApiKeyField()->getAttribute("type")
        );
    }
    
    public function testApiKeyVisibilityCanBeToggled() {
        $this->clickApiKeyAction(1);
        $this->assertEquals(
            "text",
            $this->getApiKeyField()->getAttribute("type")
        );
        
        $this->clickApiKeyAction(1);
        $this->assertEquals(
            "password",
            $this->getApiKeyField()->getAttribute("type")
        );
    }
    
    public function testApiKeyCanBeReset() {
        $value = $this->getApiKeyField()->getText();
        $this->clickApiKeyAction(2);
        
        $this->assertNotEquals(
            $this->getApiKeyField(),
            $value
        );
    }
    
    private function getApiKeyField() {
        return $this->webDriver->findElement(
            \WebDriverBy::id("api-key")
        );
    }
    
    private function clickApiKeyAction($number) {
        $this->webDriver->findElement(
            \WebDriverBy::cssSelector(".api-actions a:nth-child($number)")
        )->click();
    }
}
