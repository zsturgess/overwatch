<?php

namespace Overwatch\ResultBundle\Entity;

/**
 * FakeEntityManager
 * A fake doctrine EntityManager to use when running tests:run with --discard-results
 * 
 * @codeCoverageIgnore
 */
class FakeEntityManager {
    public function persist($obj = NULL) { }
    public function flush($obj = NULL) { echo "WARNING: Command ran with --discard-results, results NOT saved to database" . PHP_EOL; }
}
