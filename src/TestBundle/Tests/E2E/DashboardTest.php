<?php

namespace Overwatch\TestBundle\Tests\E2E;

use Facebook\WebDriver\WebDriverBy;
use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;
use Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures;
use Overwatch\UserBundle\Tests\Base\WebDriverTestCase;

/**
 * DashboardTest
 * Tests the dashboard view
 */
class DashboardTest extends WebDriverTestCase
{
    public function testDisplayGroupsAndTests()
    {
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->assertEquals('Dashboard', $this->getHeaderText());
        $this->assertCount(3, $this->getGroups());
        $this->assertCount(2, $this->getTestsForGroup(0));
        $this->assertCount(1, $this->getTestsForGroup(1));
        $this->assertCount(0, $this->getTestsForGroup(2));
        
        $this->assertContains(TestGroupFixtures::$groups['group-1']->getName(), $this->getGroups()[0]->getText());
        $this->assertContains(TestGroupFixtures::$groups['group-2']->getName(), $this->getGroups()[1]->getText());
        $this->assertContains(TestGroupFixtures::$groups['group-3']->getName(), $this->getGroups()[2]->getText());
        
        $this->assertContains('2 users', $this->getGroups()[0]->getText());
        $this->assertContains('1 user', $this->getGroups()[1]->getText());
        $this->assertContains('0 users', $this->getGroups()[2]->getText());
    }
    
    public function testDisplayGroupsAndTestsAsUser()
    {
        $this->logInAsUser('user-2');
        $this->waitForLoadingAnimation();
        
        $this->assertEquals('Dashboard', $this->getHeaderText());
        $this->assertCount(1, $this->getGroups());
        $this->assertCount(2, $this->getTestsForGroup(0));
        
        $this->assertContains(TestGroupFixtures::$groups['group-1']->getName(), $this->getGroups()[0]->getText());
        $this->assertContains('2 users', $this->getGroups()[0]->getText());
        
        $this->assertFalse($this->webDriver->findElement(
            //Edit group button
            WebDriverBy::cssSelector('.group a:nth-child(2)')
        )->isDisplayed());
        $this->assertFalse($this->getFirstTestDeleteButton()->isDisplayed());
        $this->assertFalse($this->webDriver->findElement(
            //Second test's delete button
            WebDriverBy::cssSelector('ul.tests li:nth-child(2) a:nth-child(2)')
        )->isDisplayed());
        $this->assertFalse($this->webDriver->findElement(
            //Add test button
            WebDriverBy::cssSelector('ul.tests li:last-child a:nth-child(2)')
        )->isDisplayed());
        $this->assertFalse($this->getAddGroupButton()->isDisplayed());
    }
    
    public function testResultAgeWarningHidden()
    {
        foreach (['user-1', 'user-2', 'user-3'] as $user) {
            $this->logInAsUser($user);
            $this->waitForLoadingAnimation();

            $this->assertFalse($this->webDriver->findElement(
                //Test Result Age Warning
                WebDriverBy::cssSelector("div[data-ng-show='shouldWarnOfTestAge()']")
            )->isDisplayed());

            $this->webDriver->get('http://127.0.0.1:8000/logout');
        }
    }
    
    public function testResultAgeWarningShown()
    {
        $query = "UPDATE TestResult SET created_at = '" . date('Y-m-d H:i:s', time() - (10 * 60 * 60)) . "' WHERE 1";
        $sql = $this->em->getConnection()->prepare($query);
        $sql->execute();
        
        foreach (['user-1', 'user-2'] as $user) {
            $this->logInAsUser($user);
            $this->waitForLoadingAnimation();

            $this->assertTrue($this->webDriver->findElement(
                //Test Result Age Warning
                WebDriverBy::cssSelector("div[data-ng-show='shouldWarnOfTestAge()']")
            )->isDisplayed());

            $this->webDriver->get('http://127.0.0.1:8000/logout');
        }
        
        //User 3 has no results, so should not be shown the message
        $this->logInAsUser('user-3');
        $this->waitForLoadingAnimation();

        $this->assertFalse($this->webDriver->findElement(
            //Test Result Age Warning
            WebDriverBy::cssSelector("div[data-ng-show='shouldWarnOfTestAge()']")
        )->isDisplayed());
    }
    
    public function testDisplayResults()
    {
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $tests = $this->getGroups()[0]->findElements(
            WebDriverBy::cssSelector('.tests li.ng-scope div.passed')
        );
        
        $this->assertCount(2, $tests);
        $this->assertContains(TestFixtures::$tests['test-1']->getName(), $tests[0]->getText());
        $this->assertEquals($this->getHoverTextForTest(TestFixtures::$tests['test-1']), $this->getHoverTextForTest($tests[0]));
        $this->assertContains(TestFixtures::$tests['test-2']->getName(), $tests[1]->getText());
        $this->assertEquals($this->getHoverTextForTest(TestFixtures::$tests['test-2']), $this->getHoverTextForTest($tests[1]));
        
        $tests = $this->getGroups()[1]->findElements(
            WebDriverBy::cssSelector('.tests li.ng-scope div')
        );
        $this->assertCount(1, $tests);
        $this->assertContains(TestFixtures::$tests['test-3']->getName(), $tests[0]->getText());
        $this->assertEquals($this->getHoverTextForTest(TestFixtures::$tests['test-3']), $this->getHoverTextForTest($tests[0]));
    }
    
    public function testDeleteGroup()
    {
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->getGroups()[2]->findElement(
            //Delete group button
            WebDriverBy::cssSelector('ul.groups li:nth-child(3) li div a:nth-child(1)')
        )->click();
        
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->dismiss();
        $this->assertCount(3, $this->getGroups());
        
        $this->getGroups()[2]->findElement(
            //Delete group button
            WebDriverBy::cssSelector('ul.groups li:nth-child(3) li div a:nth-child(1)')
        )->click();
        
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->accept();
        
        $this->waitForLoadingAnimation();
        $this->assertCount(2, $this->getGroups());
    }
    
    public function testAddGroup()
    {
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->getAddGroupButton()->click();
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->dismiss();
        $this->assertCount(3, $this->getGroups());
        
        $this->getAddGroupButton()->click();
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->sendKeys('Not quite untitled group');
        $this->webDriver->switchTo()->alert()->accept();
        $this->waitForLoadingAnimation();
        $this->assertCount(4, $this->getGroups());
        $this->assertContains('Not quite untitled group', $this->getGroups()[3]->getText());
    }
    
    public function testDeleteTest()
    {
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->getFirstTestDeleteButton()->click();
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->dismiss();
        $this->assertCount(2, $this->getTestsForGroup(0));
        
        $this->getFirstTestDeleteButton()->click();
        $this->waitForAlert();
        $this->webDriver->switchTo()->alert()->accept();
        $this->waitForLoadingAnimation();
        $this->assertCount(1, $this->getTestsForGroup(0));
    }
    
    public function testRunTest()
    {
        $this->logInAsUser('user-1');
        $this->waitForLoadingAnimation();
        
        $this->webDriver->findElement(
            //First test's run button
            WebDriverBy::cssSelector('ul.tests li:nth-child(1) a:nth-child(4)')
        )->click();
        $this->waitForLoadingAnimation();
        
        $this->assertEquals(TestFixtures::$tests['test-1']->getName(), $this->getHeaderText());
        $this->assertCount(4, $this->webDriver->findElements(
            WebDriverBy::cssSelector('.result span')
        ));
    }
    
    private function getGroups()
    {
        return $this->webDriver->findElements(
            WebDriverBy::cssSelector('.groups > li.ng-scope')
        );
    }
    
    private function getTestsForGroup($group)
    {
        return $this->getGroups()[$group]->findElements(
            WebDriverBy::cssSelector('.tests li.ng-scope')
        );
    }
    
    private function getHoverTextForTest($test)
    {
        //If passed a fixture, construct expected value
        if ($test instanceof \Overwatch\TestBundle\Entity\Test) {
            return 'Expect ' . $test->getActual() . ' ' . $test->getExpectation() . ' ' . $test->getExpected();
        }
        
        //Else, find actual hover text
        return $test->findElement(
            WebDriverBy::tagName('span')
        )->getAttribute('title');
    }
    
    private function getAddGroupButton()
    {
        return $this->webDriver->findElement(
            WebDriverBy::cssSelector('ul.groups > li:last-child a')
        );
    }
    
    private function getFirstTestDeleteButton()
    {
        return $this->webDriver->findElement(
            WebDriverBy::cssSelector('ul.tests li:nth-child(1) a:nth-child(2)')
        );
    }
}
