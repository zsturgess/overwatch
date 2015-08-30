<?php

namespace Overwatch\ResultBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TestResultRepository
 * Some shortcut and convienience methods to find test results
 */
class TestResultRepository extends EntityRepository {
    public function getResults($criteria, $pageSize = 10, $page = 1) {
        return $this->findBy(
            $criteria,
            [
                "createdAt" => "desc"
            ],
            $pageSize,
            ($page - 1) * $pageSize
        );
    }
    
    public function getLatest($criteria, $pageSize = 10, $page = 1) {
        return $this->findBy(
            $criteria,
            [
                "createdAt" => "desc"
            ],
            $pageSize,
            ($page - 1) * $pageSize
        );
    }
}
