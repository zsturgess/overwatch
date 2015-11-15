<?php

namespace Overwatch\TestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Overwatch\TestBundle\Entity\TestGroup;

/**
 * TestGroupFixtures
 */
class TestGroupFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    static public $groups;
    
    public function load(ObjectManager $em)
    {
        $group1 = new TestGroup;
        $group1
            ->setName("Group 1")
        ;
        $em->persist($group1);
        
        $group2 = new TestGroup;
        $group2
            ->setName("Group 2")
        ;
        $em->persist($group2);
        
        $group3 = new TestGroup;
        $group3
            ->setName("Group 3 - Empty")
        ;
        $em->persist($group3);
        
        $this->addReference('group-1', $group1);
        $this->addReference('group-2', $group2);
        $this->addReference('group-3', $group3);
        $em->flush();
    }
    
    public function getOrder()
    {
        return 1;
    }
    
    public function addReference($name, $object)
    {
        parent::addReference($name, $object);
        self::$groups[$name] = $object;
    }
}
