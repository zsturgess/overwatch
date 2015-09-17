<?php

namespace Overwatch\ResultBundle\Entity;

/**
 * FakeEntityManager
 * A fake doctrine EntityManager to use when running tests:run with --discard-results
 * 
 * @codeCoverageIgnore
 */
class FakeEntityManager {
    public function persist() { }
    public function flush() { echo "WARNING: Command ran with --discard-results, results NOT saved to database" . PHP_EOL; }
}
