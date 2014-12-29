<?php

namespace Overwatch\TestBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TestRepository
 */
class TestRepository extends EntityRepository
{
    public function findTests($search = NULL) {
        if ($search === NULL || empty($search)) {
            return $this->findAll();
        }
        
        $tests = [];
        
        foreach ($search as $term) {
            //Find test by name first
            $test = $this->findByName($term);
            if ($test !== NULL) {
                $tests[] = $test;
                continue;
            }
             
            $testsInGroup = $this->findByGroupName($term);
            foreach ($testsInGroup as $test) {
                $tests[] = $test;
            }
        }
        
        return $tests;
    }
    
    public function findByName($name) {
        return $this->findOneBy([
            "name" => $name
        ]);
    }
    
    public function findByGroupName($name) {
        $group = $this->_em->getRepository("OverwatchTestBundle:TestGroup")
            ->findOneBy([
                "name" => $name
            ]);

        if ($group === NULL) {
            return [];
        }
        
        return $this->findBy([
            "group" => $group
        ]);
    }
}
