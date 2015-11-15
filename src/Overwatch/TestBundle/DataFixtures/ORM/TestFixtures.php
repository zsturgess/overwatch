<?php

namespace Overwatch\TestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Overwatch\TestBundle\Entity\Test;

/**
 * TestGroupFixtures
 */
class TestFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    static public $tests;

    public function load(ObjectManager $em)
    {
        $test1 = new Test;
        $test1
            ->setName("Group 1, Test 1")
            ->setActual("8.8.8.8")
            ->setExpectation("toPing")
            ->setGroup($this->getReference("group-1"))
        ;
        $em->persist($test1);
        
        $test2 = new Test;
        $test2
            ->setName("Group 1, Test 2")
            ->setActual("8.8.8.9")
            ->setExpectation("toPing")
            ->setGroup($this->getReference("group-1"))
        ;
        $em->persist($test2);
        
        $test3 = new Test;
        $test3
            ->setName("Group 2, Test 3")
            ->setActual("www.google.co.uk")
            ->setExpectation("toPing")
            ->setGroup($this->getReference("group-2"))
        ;
        $em->persist($test3);
        
        $this->addReference('test-1', $test1);
        $this->addReference('test-2', $test2);
        $this->addReference('test-3', $test3);
        $em->flush();
    }
    
    public function getOrder()
    {
        return 2;
    }
    
    public function addReference($name, $object)
    {
        parent::addReference($name, $object);
        self::$tests[$name] = $object;
    }
}
