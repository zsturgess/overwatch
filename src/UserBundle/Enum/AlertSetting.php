<?php

namespace Overwatch\UserBundle\Enum;

/**
 * AlertSetting
 * The different possible alert settings for users
 */
class AlertSetting
{
    const NONE = 0;
    const CHANGE_BAD = 1;
    const CHANGE = 2;
    const CHANGE_ALL = 3;
    const ALL = 4;
    
    public static function getAll()
    {
        return [
            self::NONE       => 'Send me no alerts, ever',
            self::CHANGE_BAD => 'Only send me an alert when the result of a test changes to FAILED (or ERROR)',
            self::CHANGE     => 'Send me alerts when the result of a test changes (default)',
            self::CHANGE_ALL => 'Send me alerts for every test failure and when the result of a test changes',
            self::ALL        => 'Send me the results of every test, regardless of if the status changes'
        ];
    }
    
    public static function isValid($setting)
    {
        if (!in_array($setting, array_keys(self::getAll()))) {
            throw new \InvalidArgumentException("$setting is not a valid AlertSetting");
        }
    }
}
