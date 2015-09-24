<?php

namespace Overwatch\ResultBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Overwatch\ResultBundle\Command\ResultCleanupCommand;
use Overwatch\UserBundle\Tests\Base\DatabaseAwareTestCase;
use Overwatch\ResultBundle\DataFixtures\ORM\TestResultFixtures;

/**
 * ResultCleanupCommandTest
 */
class ResultCleanupCommandTest extends DatabaseAwareTestCase {
    use \phpmock\phpunit\PHPMock;
    use \Overwatch\TestBundle\Tests\Command\ConsoleTestHelperTrait;
    
    const COMMAND_NAME = 'overwatch:results:cleanup';
    
    private $application;
    private $command;
    private $archive;
    private $resultRepo;
    
    public function setUp() {
        parent::setUp();
        
        //Set up the console application, command and command test runner
        $command = new ResultCleanupCommand();
        $command->setContainer($this->getContainer());
        
        $this->application = new Application('Overwatch', '0.0.1-test.' . time());
        $this->application->add($command);

        $this->command = new CommandTester($command);
        $this->resultRepo = $this->em->getRepository('OverwatchResultBundle:TestResult');
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Warning: Neither the --delete or --compress options were passed. No operation will be completed.
     */
    public function testWithNoOptions() {
        $this->execute();
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not write to the archive file.
     */
    public function testArchiveWhenUnwritable() {
        $this->createUnwritableFilePutContentsMock();
        $this->execute([
            '--archive' => true,
            '--compress' => '-2 days'
        ]);
    }
    
    public function testDelete() {
        $returnCode = $this->execute([
            '--delete' => '-90 minutes'
        ]);
        
        $this->assertEquals(0, $returnCode);
        $this->assertCountLinesOfOutput(4);
        
        $this->assertHasStandardOutput();
        $this->assertContains("Finding results older than", $this->output[1]);
        $this->assertFinishedRunningCountOperations(1);
        
        $this->assertFalse($this->resultRepo->getResultsOlderThan(new \DateTime("-90 minutes"))->next());
        $this->assertNotFalse($this->resultRepo->getResultsOlderThan(new \DateTime("-1 minute"))->next());
    }
    
    public function testCompress() {
        $returnCode = $this->execute([
            '--compress' => 'now'
        ]);
        
        $this->assertEquals(0, $returnCode);
        $this->assertCountLinesOfOutput(4);
        
        $this->assertHasStandardOutput();
        $this->assertContains("Finding results older than", $this->output[1]);
        $this->assertFinishedRunningCountOperations(2);
        
        $results = $this->resultRepo->getResults([]);
        $this->assertCount(2, $results);
        $this->assertEquals(TestResultFixtures::$results['result-3']->getId(), $results[0]->getId());
        $this->assertEquals(TestResultFixtures::$results['result-2']->getId(), $results[1]->getId());
    }
    
    public function testDeleteAndArchive() {
        $this->createFilePutContentsMock();
        
        $expectedArchive = (string) $this->resultRepo->getResultsOlderThan(new \DateTime("-1 hour"))->next()[0];
        
        $returnCode = $this->execute([
            '--archive' => true,
            '--delete' => '-90 minutes'
        ]);
        
        $this->assertEquals(0, $returnCode);
        $this->assertCountLinesOfOutput(5);
        
        $this->assertHasStandardOutput();
        $this->assertContains("Finding results older than", $this->output[2]);
        $this->assertFinishedRunningCountOperations(1);
        
        $this->assertFalse($this->resultRepo->getResultsOlderThan(new \DateTime("-90 minutes"))->next());
        $this->assertNotFalse($this->resultRepo->getResultsOlderThan(new \DateTime("-1 minute"))->next());
        
        $this->assertCount(2, $this->archive);
        $this->assertStringStartsWith($this->application->getName(), $this->archive[0]);
        $this->assertContains($this->application->getVersion(), $this->archive[0]);
        $this->assertEquals($expectedArchive, $this->archive[1]);
    }
    
    public function testCompressAndArchive() {
        $this->createFilePutContentsMock();
        
        $expectedArchiveLines = [
            (string) $this->resultRepo->find(TestResultFixtures::$results['result-1']->getId()),
            (string) $this->resultRepo->find(TestResultFixtures::$results['result-4']->getId())
        ];
        
        $returnCode = $this->execute([
            '--archive' => true,
            '--compress' => 'now'
        ]);
        
        $this->assertEquals(0, $returnCode);
        $this->assertCountLinesOfOutput(5);
        
        $this->assertHasStandardOutput();
        $this->assertContains("Finding results older than", $this->output[2]);
        $this->assertFinishedRunningCountOperations(2);
        
        $results = $this->resultRepo->getResults([]);
        $this->assertCount(2, $results);
        $this->assertEquals(TestResultFixtures::$results['result-3']->getId(), $results[0]->getId());
        $this->assertEquals(TestResultFixtures::$results['result-2']->getId(), $results[1]->getId());
        
        $this->assertCount(3, $this->archive);
        $this->assertStringStartsWith($this->application->getName(), $this->archive[0]);
        $this->assertContains($this->application->getVersion(), $this->archive[0]);
        $this->assertEquals($expectedArchiveLines[0], $this->archive[1]);
        $this->assertEquals($expectedArchiveLines[1], $this->archive[2]);
    }
    
    private function createUnwritableFilePutContentsMock() {
        $mock = $this->getFunctionMock('Overwatch\ResultBundle\Command', 'file_put_contents');
        $mock->expects($this->any())->willReturn(false);
    }
    
    private function createFilePutContentsMock() {
        $this->archive = [];
        
        $mock = $this->getFunctionMock('Overwatch\ResultBundle\Command', 'file_put_contents');
        $mock->expects($this->any())->willReturnCallback(function($file, $contents, $mode) {
            $this->assertRegExp("/overwatch_archive_[0-9]{14}.log/i", $file);
            $this->assertStringEndsWith(PHP_EOL, $contents);
            $this->assertEquals(FILE_APPEND, $mode);
            
            $this->archive[] = trim($contents);
            return true;
        });
    }
    
    private function assertFinishedRunningCountOperations($count) {
        $this->assertEquals(" > Applying $count cleanup operations", $this->output[count($this->output) - 2]);
        $this->assertEquals("Cleanup finished", $this->output[count($this->output) - 1]);
    }
}
