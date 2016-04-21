<?php

namespace Overwatch\UserBundle\Tests\E2E;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
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
            WebDriverBy::cssSelector('.sidebar li:nth-child(3) a')
        )->click();
        $this->waitForLoadingAnimation();
    }
    
    public function testChangeSetting()
    {
        $this->assertElementHasClass($this->getSettingRow(5), 'current-setting');
        
        $this->getSettingRow(1)->click();
        $this->waitForLoadingAnimation();
        $this->assertElementNotHasClass($this->getSettingRow(5), 'current-setting');
        $this->assertElementHasClass($this->getSettingRow(1), 'current-setting');
    }
    
    private function getSettingRow($number)
    {
        return $this->webDriver->findElement(
            WebDriverBy::cssSelector(".settings li:nth-child($number)")
        );
    }
    
    private function assertElementHasClass(WebDriverElement $element, $class, $has = true) {
        $classes = $element->getAttribute('class');
        
        $this->assertNotNull($classes);
        $this->assertEquals(
            $has,
            in_array(
                strtolower($class),
                array_map(
                    'trim',
                    explode(" ", strtolower($classes))
                )
            )
        );
    }
    
    private function assertElementNotHasClass(WebDriverElement $element, $class) {
        $this->assertElementHasClass($element, $class, false);
    }
}
