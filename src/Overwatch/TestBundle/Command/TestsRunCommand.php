<?php

namespace Overwatch\TestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Overwatch\ExpectationBundle\Exception as ExpectationException;

class TestsRunCommand extends ContainerAwareCommand {
    /**
     * @var Overwatch\ExpectationBundle\Helper\ExpectationManger 
     */
    private $expectations;
    
    /**
     * @var Overwatch\TestBundle\Entity\TestRepository 
     */
    private $testRepo;


    public function setContainer(ContainerInterface $container = NULL) {
        parent::setContainer($container);
        $this->expectations = $this->getContainer()->get("overwatch_expectation.expectation_manager");
        $this->testRepo = $this->getContainer()->get("doctrine.orm.entity_manager")->getRepository("OverwatchTestBundle:Test");
    }
    
    protected function configure() {
        $this
            ->setName('overwatch:tests-run')
            ->setDescription('Run a set of overwatch tests')
            ->addOption('test', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'If set, the task will only run the tests given')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $tests = $this->testRepo->findTests($input->getOption("test"));
        
        $output->writeln($this->getApplication()->getLongVersion() . ", running <info>" . count($tests) . "</info> tests");

        foreach ($tests as $test) {
            //@TODO: Pass off to a runTest method which will return a TestResult object.
            // Then use the object to figure out what to print.
            // Then persist the object
            $output->write($test->getName());
            $output->write(": ");
            $output->write(
                $this->handleResult(
                    $this->expectations->run(
                        $test->getActual(),
                        $test->getExpectation(),
                        $test->getExpected()
                    )
                ),
                true
            );
        }
        
        //@TODO: call flush() to save the results, handle errors
    }
    
    //@TODO: Refactor with a TestResult object.
    private function handleResult($result) {
        if ($result instanceof ExpectationException\ExpectationFailedException) {
            return "<error>FAILED</error> " . $result->getMessage();
        } else if ($result instanceof ExpectationException\ExpectationUnsatisfactoryException) {
            return "<comment>Unsatisfactory</comment> " . $result->getMessage();
        } else if ($result instanceof \Exception) {
            return "<error>ERROR</error> " . $result->getMessage();
        } else {
            return "<info>OK</info> " . $result;
        }
    }
}
