<?php

namespace Overwatch\UserBundle\Tests\Entity;

use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Enum\ResultStatus;
use Overwatch\TestBundle\Entity\TestGroup;
use Overwatch\UserBundle\Entity\User;
use Overwatch\UserBundle\Enum\AlertSetting;

/**
 * UserTest
 * A unit test for the User Entity.
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    private $user;

    const EMAIL = 'a@b.com';

    public function setUp()
    {
        $this->user = new User;
        $this->user->setEmail(self::EMAIL);
    }

    public function testValid()
    {
        $this->user->setAlertSetting(AlertSetting::CHANGE_BAD);

        $this->assertEquals(self::EMAIL, $this->user->getEmail());
        $this->assertEquals(self::EMAIL, $this->user->getUsername());
        $this->assertEquals(AlertSetting::CHANGE_BAD, $this->user->getAlertSetting());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'id' => NULL,
                'email' => self::EMAIL,
                'alertSetting' => AlertSetting::CHANGE_BAD,
                'lastLogin' => '',
                'locked' => false,
                'roles' => ['ROLE_USER']
            ]),
            json_encode($this->user)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetAlertSettingInvalid()
    {
        $this->user->setAlertSetting(10);
    }

    public function testShouldBeAlertedReturnsFalseWithSettingNone()
    {
        $this->user->setAlertSetting(AlertSetting::NONE);

        foreach (ResultStatus::getAll() as $status) {
            $result = new TestResult();
            $result
                ->setStatus($status)
                ->setInfo('Stahp shut up and take my money scumbag stacy trollface.')
            ;

            $this->assertFalse($this->user->shouldBeAlerted($result));
        }
    }

    public function testShouldBeAlertedReturnsTrueWithSettingAll()
    {
        $this->user->setAlertSetting(AlertSetting::ALL);

        foreach (ResultStatus::getAll() as $status) {
            $result = $this->getMockResult();
            $result
                ->setStatus($status)
                ->setInfo('Stahp shut up and take my money scumbag stacy trollface.')
            ;

            $this->assertTrue($this->user->shouldBeAlerted($result));
        }
    }

    public function testShouldBeAlertedWithChangingFailedResult()
    {
        $result = $this->getMockResult(true);
        $result
            ->setStatus(ResultStatus::FAILED)
            ->setInfo('Stahp shut up and take my money scumbag stacy trollface.')
        ;

        $this->user->setAlertSetting(AlertSetting::CHANGE_BAD);
        $this->assertTrue($this->user->shouldBeAlerted($result));

        $this->user->setAlertSetting(AlertSetting::CHANGE);
        $this->assertTrue($this->user->shouldBeAlerted($result));

        $this->user->setAlertSetting(AlertSetting::CHANGE_ALL);
        $this->assertTrue($this->user->shouldBeAlerted($result));
    }

    public function testShouldBeAlertedWithChangingPassedResult()
    {
        $result = $this->getMockResult(true);
        $result
            ->setStatus(ResultStatus::PASSED)
            ->setInfo('Stahp shut up and take my money scumbag stacy trollface.')
        ;

        $this->user->setAlertSetting(AlertSetting::CHANGE_BAD);
        $this->assertFalse($this->user->shouldBeAlerted($result));

        $this->user->setAlertSetting(AlertSetting::CHANGE);
        $this->assertTrue($this->user->shouldBeAlerted($result));

        $this->user->setAlertSetting(AlertSetting::CHANGE_ALL);
        $this->assertTrue($this->user->shouldBeAlerted($result));
    }

    public function testShouldBeAlertedWithUnchangingFailedResult()
    {
        $result = $this->getMockResult();
        $result
            ->setStatus(ResultStatus::FAILED)
            ->setInfo('Stahp shut up and take my money scumbag stacy trollface.')
        ;

        $this->user->setAlertSetting(AlertSetting::CHANGE_BAD);
        $this->assertFalse($this->user->shouldBeAlerted($result));

        $this->user->setAlertSetting(AlertSetting::CHANGE);
        $this->assertFalse($this->user->shouldBeAlerted($result));

        $this->user->setAlertSetting(AlertSetting::CHANGE_ALL);
        $this->assertTrue($this->user->shouldBeAlerted($result));
    }

    public function testShouldBeAlertedWithUnchangingPassedResult()
    {
        $result = $this->getMockResult();
        $result
            ->setStatus(ResultStatus::PASSED)
            ->setInfo('Stahp shut up and take my money scumbag stacy trollface.')
        ;

        $this->user->setAlertSetting(AlertSetting::CHANGE_BAD);
        $this->assertFalse($this->user->shouldBeAlerted($result));

        $this->user->setAlertSetting(AlertSetting::CHANGE);
        $this->assertFalse($this->user->shouldBeAlerted($result));

        $this->user->setAlertSetting(AlertSetting::CHANGE_ALL);
        $this->assertFalse($this->user->shouldBeAlerted($result));
    }

    public function testShouldBeAlertedWithUserNotInResultGroup()
    {
        $result = $this->getMockResult(true, false);
        $result
            ->setStatus(ResultStatus::FAILED)
            ->setInfo('Stahp shut up and take my money scumbag stacy trollface.')
        ;

        foreach (AlertSetting::getAll() as $setting => $description) {
            $this->user->setAlertSetting($setting);
            $this->assertFalse($this->user->shouldBeAlerted($result));
        }
    }

    private function getMockTest($userInGroup = true)
    {
        $group = new TestGroup;
        $group->setName('MemeGroup1');

        $test = $this->getMock('Overwatch\TestBundle\Entity\Test', ['getGroup']);
        $test->method('getGroup')->willReturn($group);

        if ($userInGroup === true) {
            $group->addUser($this->user);
        }

        return $test;
    }

    private function getMockResult($isAChange = false, $userInGroup = true)
    {
        $result = $this->getMock('Overwatch\ResultBundle\Entity\TestResult', ['isAChange', 'getTest']);
        $result->method('isAChange')->willReturn($isAChange);
        $result->method('getTest')->willReturn($this->getMockTest($userInGroup));

        return $result;
    }
}
