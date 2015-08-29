<?php

namespace Overwatch\UserBundle\Tests\E2E;

use Facebook\WebDriver\WebDriverBy;
use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;
use Overwatch\UserBundle\DataFixtures\ORM\UserFixtures;

/**
 * ManageUsersTest
 * Tests the Manage Users screen
 */
class ManageUsersTest extends WebDriverTestCase {
    public function setUp() {
        parent::setUp();
        $this->logInAsUser1();
    }
    
    public function testDisplaysUsers() {
        $this->assertEquals("Manage Users", $this->getHeaderText());
        $this->assertCount(3, $this->getUsers());
        $this->assertContains(UserFixtures::$users['user-1']->getEmail(), $this->getUsers()[0]->getText());
        $this->assertContains("failed", $this->getUsers(" div.user")[0]->getAttribute("class"));
        $this->assertContains(UserFixtures::$users['user-2']->getEmail(), $this->getUsers()[1]->getText());
        $this->assertContains("passed", $this->getUsers(" div.user")[1]->getAttribute("class"));
        $this->assertContains(UserFixtures::$users['user-3']->getEmail(), $this->getUsers()[2]->getText());
        $this->assertContains("unsatisfactory", $this->getUsers(" div.user")[2]->getAttribute("class"));
    }
    
    public function testCannotEditMe() {
        $this->assertFalse($this->getUsers(":first-child div.user a[title]")[0]->isDisplayed());
        $this->assertFalse($this->getUsers(":first-child div.user a[title]")[1]->isDisplayed());
        $this->assertFalse($this->getUsers(":first-child div.user a[title]")[2]->isDisplayed());
        
        $itsyou = $this->getUsers(":first-child div.user a:not([title])")[0];
        $this->assertTrue($itsyou->isDisplayed());
        $this->assertContains("It's you!", $itsyou->getText());
    }
    
    public function testLockUser() {
        $lockButton = $this->getUsers(":nth-child(2) div a:nth-child(3)")[0];
        $this->assertEquals("Lock", $lockButton->getText());
        
        $lockButton->click();
        $this->waitForLoadingAnimation();
        $this->assertEquals("Unlock", $this->getUsers(":nth-child(2) div a:nth-child(3)")[0]->getText());
        
        $this->webDriver->get("http://127.0.0.1:8000/logout");
        $this->logInAsUser('user-2');
        $this->assertEquals("http://127.0.0.1:8000/login", $this->webDriver->getCurrentURL());
        $this->assertEquals("User account is locked.", $this->webDriver->findElement(WebDriverBy::cssSelector("#page > div"))->getText());
        
        $this->logInAsUser1();
        $this->getUsers(":nth-child(2) div a:nth-child(3)")[0]->click();
        $this->waitForLoadingAnimation();
        $this->assertEquals("Lock", $this->getUsers(":nth-child(2) div a:nth-child(3)")[0]->getText());
        
        $this->webDriver->get("http://127.0.0.1:8000/logout");
        $this->logInAsUser('user-2');
        $this->assertNotEquals("http://127.0.0.1:8000/login", $this->webDriver->getCurrentURL());
    }
    
    public function testDeleteUser() {
        $deleteButton = $this->getUsers(":nth-child(2) div a:nth-child(2)")[0];
        $this->assertEquals("Delete", $deleteButton->getText());
        
        $deleteButton->click();
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->dismiss();
        $this->assertCount(3, $this->getUsers());
        
        $deleteButton->click();
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->accept();
        $this->waitForLoadingAnimation();
        $this->assertCount(2, $this->getUsers());
        
        $this->webDriver->get("http://127.0.0.1:8000/logout");
        $this->logInAsUser('user-2');
        $this->assertEquals("http://127.0.0.1:8000/login", $this->webDriver->getCurrentURL());
        $this->assertEquals("Bad credentials.", $this->webDriver->findElement(WebDriverBy::cssSelector("#page > div"))->getText());
    }
    
    public function testEditUserRole() {
        $this->getUsers(":nth-child(2) div a:nth-child(4)")[0]->click();
        $this->webDriver->findElement(
            WebDriverBy::cssSelector("div.dialog button:nth-child(6)")
        )->click();
        $this->waitForLoadingAnimation();
        $this->assertContains("unsatisfactory", $this->getUsers(" div.user")[2]->getAttribute("class"));
    }
    
    public function testRegisterNewUser() {
        $this->webDriver->findElement(
            //Register button
            WebDriverBy::cssSelector("ul.users li:last-child a")
        )->click();
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->dismiss();
        $this->assertCount(3, $this->getUsers());
        
        $this->webDriver->findElement(
            //Register button
            WebDriverBy::cssSelector("ul.users li:last-child a")
        )->click();
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->sendKeys('void@example.com');
        $this->webDriver->switchTo()->alert()->accept();
        $this->waitForLoadingAnimation();
        $this->assertCount(4, $this->getUsers());
    }
    
    private function getUsers($suffix = "") {
        return $this->webDriver->findElements(
            WebDriverBy::cssSelector(".users li.ng-scope" . $suffix)
        );
    }
    
    private function logInAsUser1() {
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->webDriver->findElement(
            WebDriverBy::cssSelector("#sidebar li:nth-child(2) a")
        )->click();
        $this->waitForLoadingAnimation();
    }
}
