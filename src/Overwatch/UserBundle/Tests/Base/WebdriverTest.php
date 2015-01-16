<?php

namespace Overwatch\UserBundle\Tests\Base;

/**
 * WebdriverTest
 * A simple try-out of WebDriver
 */
class WebdriverTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \RemoteWebDriver
     */
    protected $webDriver;
    
    public function setUp() {
        $capabilities = [\WebDriverCapabilityType::BROWSER_NAME => 'firefox'];
        $this->webDriver = \RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
    }
    
    public function tearDown() {
        $this->webDriver->close();
    }
    
    public function testGitHubHome() {
        $this->webDriver->get("https://github.com");
        // checking that page title contains word 'GitHub'
        $this->assertContains('GitHub', $this->webDriver->getTitle());
    }
}
