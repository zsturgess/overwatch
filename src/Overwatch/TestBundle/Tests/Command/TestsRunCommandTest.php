<?php

namespace Overwatch\TestBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\CommandTester;
use Overwatch\TestBundle\Command\TestsRunCommand;
use Overwatch\TestBundle\DataFixtures\ORM\TestFixtures;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;
use phpmock\phpunit\PHPMock;

/**
 * TestsRunCommandTest
 */
class TestsRunCommandTest extends DatabaseAwareTestCase {
    use PHPMock;
    use ConsoleTestHelperTrait;
    
    const COMMAND_NAME = 'overwatch:tests:run';
    
    private $application;
    private $command;
    private $output = null;
    private $resultRepo;
    
    public function setUp() {
        parent::setUp();
        $this->createSocketMocks();
        
        //Set up the console application, command and command test runner
        $command = new TestsRunCommand();
        $command->setContainer($this->getContainer());
        
        $this->application = new Application('Overwatch', '0.0.1-test.'.time());
        $this->application->add($command);

        $this->command = new CommandTester($command);
        $this->resultRepo = $this->em->getRepository('OverwatchResultBundle:TestResult');
    }
    
    public function testWhenAllPass() {
        $this->createSocketReadMock();
        
        $returnCode = $this->execute();
        
        $this->assertEquals(0, $returnCode);
        $this->assertCountLinesOfOutput(2);
        
        $this->assertHasStandardOutput();
        
        $this->assertCountRunTests();
        $this->assertResults(0, 0, 0, count(TestFixtures::$tests));
        
        $this->assertRecentResultsPersisted();
    }
    
    public function testVerboselyWhenAllPass() {
        $this->createSocketReadMock();
        
        $returnCode = $this->execute([], [
            'verbosity' => Output::VERBOSITY_VERBOSE
        ]);
        
        $this->assertEquals(0, $returnCode);
        $this->assertCountLinesOfOutput(5);
        
        $this->assertHasStandardOutput();
        $this->assertStringStartsWith(' > ' . TestFixtures::$tests['test-1'] . ' : PASSED - Pinged in ', $this->output[1]);
        $this->assertRegExp('/0\.[0-9]+s$/', $this->output[1]);
        
        $this->assertStringStartsWith(' > ' . TestFixtures::$tests['test-2'] . ' : PASSED - Pinged in ', $this->output[2]);
        $this->assertRegExp('/0\.[0-9]+s$/', $this->output[2]);
        
        $this->assertStringStartsWith(' > ' . TestFixtures::$tests['test-3'] . ' : PASSED - Pinged in ', $this->output[3]);
        $this->assertRegExp('/0\.[0-9]+s$/', $this->output[3]);
        
        $this->assertCountRunTests();
        $this->assertResults(0, 0, 0, count(TestFixtures::$tests));
        
        $this->assertRecentResultsPersisted();
    }
    
    public function testDiscardsResults() {
        $this->createSocketReadMock();
        
        $returnCode = $this->execute(['--discard-results']);
        
        $this->assertEquals(0, $returnCode);
        $this->assertCountLinesOfOutput(2);
        
        $this->assertHasStandardOutput();
        
        $this->assertCountRunTests();
        $this->assertResults(0, 0, 0, count(TestFixtures::$tests));
        
        $this->assertRecentResultsNotPersisted();
    }
    
    public function testNamedTests() {
        $this->createSocketReadMock();
        
        $returnCode = $this->execute([
            '--test' => [
                'Group 2',
                'Group 1, Test 2'
            ]
        ], [
            'verbosity' => Output::VERBOSITY_VERBOSE
        ]);
        
        $this->assertEquals(0, $returnCode);
        $this->assertCountLinesOfOutput(4);
        
        $this->assertHasStandardOutput();
        
        $this->assertCountRunTests(2);
        $this->assertResults(0, 0, 0, 2);
        
        $this->assertRecentResultsPersisted(2);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not find any tests to run.
     */
    public function testNoNamedTestsFound() {
        $this->execute([
            '--test' => [
                'Group 3'
            ]
        ]);
    }
    
    public function testMixedResults() {
        $this->createSocketReadMockForMixedResults();
        
        $returnCode = $this->execute();
        
        $this->assertEquals(2, $returnCode);
        $this->assertCountLinesOfOutput(4);
        
        $this->assertHasStandardOutput();
        
        $this->assertStringStartsWith(' > ' . TestFixtures::$tests['test-2'] . ' : UNSATISFACTORY - ', $this->output[1]);
        $this->assertRegExp('/responded in 1\.[0-9]+ s, above the unsatisfactory threshold \(1 s\)$/', $this->output[1]);
        
        $this->assertStringStartsWith(' > ' . TestFixtures::$tests['test-3'] . ' : FAILED - ', $this->output[2]);
        $this->assertStringEndsWith('failed to respond in the timeout threshold (2 s)', $this->output[2]);
        
        
        $this->assertCountRunTests();
        $this->assertResults(1, 0, 1, (count(TestFixtures::$tests) - 2));
        
        $this->assertRecentResultsPersisted();
    }
    
    public function testVerboseMixedResults() {
        $this->createSocketReadMockForMixedResults();
        
        $returnCode = $this->execute([], [
            'verbosity' => Output::VERBOSITY_VERBOSE
        ]);
        
        $this->assertEquals(2, $returnCode);
        $this->assertCountLinesOfOutput(5);
        
        $this->assertHasStandardOutput();
        
        $this->assertStringStartsWith(' > ' . TestFixtures::$tests['test-1'] . ' : PASSED - Pinged in ', $this->output[1]);
        $this->assertRegExp('/0\.[0-9]+s$/', $this->output[1]);
        
        $this->assertStringStartsWith(' > ' . TestFixtures::$tests['test-2'] . ' : UNSATISFACTORY - ', $this->output[2]);
        $this->assertRegExp('/responded in 1\.[0-9]+ s, above the unsatisfactory threshold \(1 s\)$/', $this->output[2]);
        
        $this->assertStringStartsWith(' > ' . TestFixtures::$tests['test-3'] . ' : FAILED - ', $this->output[3]);
        $this->assertStringEndsWith('failed to respond in the timeout threshold (2 s)', $this->output[3]);
        
        
        $this->assertCountRunTests();
        $this->assertResults(1, 0, 1, (count(TestFixtures::$tests) - 2));
        
        $this->assertRecentResultsPersisted();
    }
    
    private function execute($params = [], $options = []) {
        $params['command'] = self::COMMAND_NAME;
        $returnCode = $this->command->execute($params, $options);
        
        $this->output = explode(PHP_EOL, $this->command->getDisplay());
        $lastLine = array_pop($this->output);
        
        if (!empty($lastLine)) {
            array_push($this->output, $lastLine);
        }
        
        return $returnCode;
    }
    
    private function createSocketMocks() {
        foreach ([
            "socket_create",
            "socket_set_option",
            "socket_connect",
            "socket_send",
            "socket_close"
        ] as $func) {
            $mock = $this->getFunctionMock('Overwatch\ServiceBundle\Expectation', $func);
            $mock->expects($this->any())->willReturn(true);
        }
    }
    
    private function createSocketReadMock() {
        $mock = $this->getFunctionMock('Overwatch\ServiceBundle\Expectation', 'socket_read');
        
        $mock->expects($this->any())->willReturnCallback(function() {
            usleep(rand(500, 300000)); //Sleep randomly between 0.0005 and 0.3s
            return true;
        });
    }
    
    private function createSocketReadMockForMixedResults() {
        $mock = $this->getFunctionMock('Overwatch\ServiceBundle\Expectation', 'socket_read');
        
        $mock->expects($this->any())->willReturnOnConsecutiveCalls(
            $this->returnCallback(function() { usleep(500); return true; }),
            $this->returnCallback(function() { usleep(1000500); return true; }),
            $this->returnCallback(function() { return false; })
        );
    }
}
