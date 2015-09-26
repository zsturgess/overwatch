<?php

namespace Overwatch\TestBundle\Tests\Security;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Overwatch\TestBundle\DataFixtures\ORM\TestGroupFixtures;
use Overwatch\TestBundle\Security\TestGroupVoter;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;
use Overwatch\UserBundle\DataFixtures\ORM\UserFixtures;

/**
 * TestGroupVoterTest
 * Functional test of TestGroupVoter
 */
class TestGroupVoterTest extends DatabaseAwareTestCase {
    private $voter;
    private $tokens = [];
    
    const FIREWALL = 'overwatch';
    
    public function setUp() {
        parent::setUp();
        $this->voter = new TestGroupVoter;
        $this->tokens = [
            "superadmin" => $this->createUserToken(UserFixtures::$users['user-1'], self::FIREWALL),
            "admin"      => $this->createUserToken(UserFixtures::$users['user-3'], self::FIREWALL),
            "user"       => $this->createUserToken(UserFixtures::$users['user-2'], self::FIREWALL)
        ];
    }
    
    public function testVoteGroupView() {
        $this->assertGranted($this->voter->vote(
            $this->tokens['superadmin'],
            TestGroupFixtures::$groups['group-1'],
            [TestGroupVoter::VIEW]
        ));
        
        $this->assertGranted($this->voter->vote(
            $this->tokens['user'],
            TestGroupFixtures::$groups['group-1'],
            [TestGroupVoter::VIEW]
        ));
        
        $this->assertDenied($this->voter->vote(
            $this->tokens['admin'],
            TestGroupFixtures::$groups['group-1'],
            [TestGroupVoter::VIEW]
        ));
        
        $this->assertGranted($this->voter->vote(
            $this->tokens['superadmin'],
            TestGroupFixtures::$groups['group-2'],
            [TestGroupVoter::VIEW]
        ));
        
        $this->assertDenied($this->voter->vote(
            $this->tokens['user'],
            TestGroupFixtures::$groups['group-2'],
            [TestGroupVoter::VIEW]
        ));
        
        $this->assertGranted($this->voter->vote(
            $this->tokens['admin'],
            TestGroupFixtures::$groups['group-2'],
            [TestGroupVoter::VIEW]
        ));
    }
    
    public function testVoteGroupEdit() {
        $this->assertGranted($this->voter->vote(
            $this->tokens['superadmin'],
            TestGroupFixtures::$groups['group-1'],
            [TestGroupVoter::EDIT]
        ));
        
        $this->assertDenied($this->voter->vote(
            $this->tokens['user'],
            TestGroupFixtures::$groups['group-1'],
            [TestGroupVoter::EDIT]
        ));
        
        $this->assertDenied($this->voter->vote(
            $this->tokens['admin'],
            TestGroupFixtures::$groups['group-1'],
            [TestGroupVoter::EDIT]
        ));
        
        $this->assertGranted($this->voter->vote(
            $this->tokens['superadmin'],
            TestGroupFixtures::$groups['group-2'],
            [TestGroupVoter::EDIT]
        ));
        
        $this->assertDenied($this->voter->vote(
            $this->tokens['user'],
            TestGroupFixtures::$groups['group-2'],
            [TestGroupVoter::EDIT]
        ));
        
        $this->assertGranted($this->voter->vote(
            $this->tokens['admin'],
            TestGroupFixtures::$groups['group-2'],
            [TestGroupVoter::EDIT]
        ));
    }
    
    public function testVoteInvalid() {
        $this->assertAbstain($this->voter->vote(
            $this->tokens['superadmin'],
            new \stdClass,
            [TestGroupVoter::VIEW]
        ));
        
        $this->assertAbstain($this->voter->vote(
            $this->tokens['superadmin'],
            TestGroupFixtures::$groups['group-2'],
            ['BACON'] //No authority to issue bacon rights.
        ));
    }
    
    private function assertGranted($vote) {
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }
    
    private function assertAbstain($vote) {
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $vote);
    }
    
    private function assertDenied($vote) {
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }
}
