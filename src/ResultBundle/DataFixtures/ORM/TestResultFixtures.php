<?php

namespace Overwatch\ResultBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Enum\ResultStatus;

/**
 * TestGroupFixtures
 */
class TestResultFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    public static $results = [];

    public function load(ObjectManager $em)
    {
        $result1 = new TestResult();
        $result1
            ->setTest($this->getReference('test-1'))
            ->setStatus(ResultStatus::PASSED)
            ->setInfo('Ermahgerd cool story bro trollface soon overly manly man.')
            ->setCreatedAt('-2 hours')
        ;
        $em->persist($result1);

        $result2 = new TestResult();
        $result2
            ->setTest($this->getReference('test-1'))
            ->setStatus(ResultStatus::FAILED)
            ->setInfo('Brace yourselves doge forever alone bad luck Brian.')
            ->setCreatedAt('-1 hour')
        ;
        $em->persist($result2);

        $result3 = new TestResult();
        $result3
            ->setTest($this->getReference('test-1'))
            ->setStatus(ResultStatus::PASSED)
            ->setInfo('Me gusta success kid upvoting Obama first world problems.')
            ->setCreatedAt('-30 minutes')
        ;
        $em->persist($result3);

        $result4 = new TestResult();
        $result4
            ->setTest($this->getReference('test-2'))
            ->setStatus(ResultStatus::PASSED)
            ->setInfo('This test is thrown in to try and trick the system. Muahhaha.')
            ->setCreatedAt('-10 minutes')
        ;
        $em->persist($result4);

        $this->addReference('result-1', $result1);
        $this->addReference('result-2', $result2);
        $this->addReference('result-3', $result3);
        $this->addReference('result-4', $result4);
        $em->flush();
    }

    public function getOrder()
    {
        return 3;
    }

    public function addReference($name, $object)
    {
        parent::addReference($name, $object);
        self::$results[$name] = $object;
    }
}
