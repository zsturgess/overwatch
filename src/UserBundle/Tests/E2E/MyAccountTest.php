<?php

namespace Overwatch\UserBundle\Tests\E2E;

use Facebook\WebDriver\WebDriverBy;
use Overwatch\UserBundle\DataFixtures\ORM\UserFixtures;
use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;

/**
 * MyAccountTest
 *
 * @author Zac Sturgess <zac.sturgess@wearetwogether.com>
 */
class MyAccountTest extends WebDriverTestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->webDriver->findElement(
            WebDriverBy::cssSelector('#sidebar li:nth-child(4) a')
        )->click();
        $this->waitForLoadingAnimation();
    }
    
    public function testProfileDetails()
    {
        $newDetails = [
            'telephoneNumber' => '+4401628813588'
        ];
        
        $profileItems = $this->webDriver->findElements(
            WebDriverBy::cssSelector('.profile-details input')
        );
        
        $this->assertEquals(
            UserFixtures::$users['user-1']->getTelephoneNumber(),
            $profileItems[0]->getAttribute('value')
        );
        
        $profileItems[0]->clear();
        $profileItems[0]->sendKeys($newDetails['telephoneNumber']);

        $profileItems[count($profileItems) - 1]->click();
        $this->waitForLoadingAnimation();
        
        $this->assertEquals($newDetails['telephoneNumber'], $profileItems[0]->getAttribute('value'));
    }
    
    public function testApiKeyHidden()
    {
        $this->assertEquals(
            'password',
            $this->getApiKeyField()->getAttribute('type')
        );
    }
    
    public function testApiKeyVisibilityCanBeToggled()
    {
        $this->clickApiKeyAction(1);
        $this->assertEquals(
            'text',
            $this->getApiKeyField()->getAttribute('type')
        );
        
        $this->clickApiKeyAction(1);
        $this->assertEquals(
            'password',
            $this->getApiKeyField()->getAttribute('type')
        );
    }
    
    public function testApiKeyCanBeReset()
    {
        $value = $this->getApiKeyField()->getAttribute('value');
        
        $this->clickApiKeyAction(2);
        $this->waitForLoadingAnimation();
        
        $this->assertNotEquals(
            $this->getApiKeyField()->getAttribute('value'),
            $value
        );
    }
    
    private function getApiKeyField()
    {
        return $this->webDriver->findElement(
            WebDriverBy::id('api-key')
        );
    }
    
    private function clickApiKeyAction($number)
    {
        $this->webDriver->findElement(
            WebDriverBy::cssSelector(".api-actions a:nth-child($number)")
        )->click();
    }
}
