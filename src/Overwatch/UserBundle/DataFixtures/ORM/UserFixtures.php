<?php

namespace Overwatch\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Overwatch\UserBundle\Entity\User;
use Overwatch\UserBundle\Enum\AlertSetting;

/**
 * TestGroupFixtures
 */
class UserFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    static public $users;

    public function load(ObjectManager $em)
    {
        $user1 = new User;
        $user1
            ->setAlertSetting(AlertSetting::ALL)
            ->setEmail("overwatch.admin@example.com")
            ->setTelephoneNumber("+4401628813587")
            ->setEnabled(true)
            ->setPlainPassword("p4ssw0rd")
            ->setSuperAdmin(true)
        ;
        $em->persist($user1);
        
        $user2 = new User;
        $user2
            ->setAlertSetting(AlertSetting::NONE)
            ->setEmail("overwatch.user@example.com")
            ->setEnabled(true)
            ->setPlainPassword("p4ssw0rd")
            ->setRoles(["ROLE_USER"])
        ;
        $em->persist($user2);
        
        $user3 = new User;
        $user3
            ->setAlertSetting(AlertSetting::NONE)
            ->setEmail("overwatch.group.admin@example.com")
            ->setEnabled(true)
            ->setPlainPassword("p4ssw0rd")
            ->setRoles(["ROLE_ADMIN"])
        ;
        $em->persist($user3);
        
        $this->getReference('group-1')->addUser($user1);
        $this->getReference('group-1')->addUser($user2);
        $this->getReference('group-2')->addUser($user3);
        
        $this->addReference('user-1', $user1);
        $this->addReference('user-2', $user2);
        $this->addReference('user-3', $user3);
        
        $em->flush();
    }
    
    public function getOrder()
    {
        return 4;
    }
    
    public function addReference($name, $object)
    {
        parent::addReference($name, $object);
        self::$users[$name] = $object;
    }
}
