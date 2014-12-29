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
        
        foreach ($search as $term) {
            $test = $this->findOneBy([
                "name" => $term
            ]);
            
            if ($test !== NULL) {
                $tests[] = $test;
            }
        }
        
        return $tests;
    }
}
