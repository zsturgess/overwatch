<?php

namespace Overwatch\TestBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TestRepository
 * Some shortcut and convienience methods to find tests
 */
class TestRepository extends EntityRepository
{
    public function findTests($search = null)
    {
        if ($search === null || empty($search)) {
            return $this->findAll();
        }
        
        if (!is_array($search)) {
            $search = [$search];
        }
        
        $tests = [];
        
        foreach ($search as $term) {
            //Find test by name first
            $test = $this->findByName($term);
            if ($test !== null) {
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
    
    public function findByName($name)
    {
        return $this->findOneBy([
            'name' => $name
        ]);
    }
    
    public function findByGroupName($name)
    {
        $group = $this->_em->getRepository('OverwatchTestBundle:TestGroup')
            ->findOneBy([
                'name' => $name
            ]);

        if ($group === null) {
            return [];
        }
        
        return $this->findBy([
            'group' => $group
        ]);
    }
}
