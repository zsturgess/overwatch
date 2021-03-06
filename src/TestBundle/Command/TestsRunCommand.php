<?php

namespace Overwatch\TestBundle\Command;

use Overwatch\ResultBundle\Enum\ResultStatus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TestsRunCommand
 * The overwatch:tests:run command, the heart of Overwatch
 */
class TestsRunCommand extends ContainerAwareCommand
{
    /**
     * @var Overwatch\ExpectationBundle\Helper\ExpectationManger 
     */
    private $expectations;
    
    private $_em;
    private $results = [];
    private $colours = [];
    
    protected function configure()
    {
        $this
            ->setName('overwatch:tests:run')
            ->setDescription('Run a set of overwatch tests')
            ->addOption('test', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Only run tests that are named this value or are in groups named this value')
            ->addOption('discard-results', null, InputOption::VALUE_NONE, 'Do not save the results to the database')
        ;
        
        //Initalize results array
        $this->results = [
            ResultStatus::ERROR          => 0,
            ResultStatus::FAILED         => 0,
            ResultStatus::PASSED         => 0,
            ResultStatus::UNSATISFACTORY => 0
        ];
        
        //Initalize colours array
        $this->colours = [
            ResultStatus::ERROR          => 'error',
            ResultStatus::FAILED         => 'error',
            ResultStatus::PASSED         => 'info',
            ResultStatus::UNSATISFACTORY => 'comment'
        ];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        
        //Set up some shortcuts to services
        if ($container !== null) {
            $this->expectations = $container->get('overwatch_expectation.expectation_manager');
            $this->_em = $container->get('doctrine.orm.entity_manager');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime;

        $tests = $this->_em->getRepository('OverwatchTestBundle:Test')->findTests($input->getOption('test'));
        if (empty($tests)) {
            throw new \InvalidArgumentException('Could not find any tests to run.');
        }
        
        $output->writeln($this->getApplication()->getLongVersion() . ', running <info>' . count($tests) . '</info> tests');
        
        foreach ($tests as $test) {
            $result = $this->expectations->run($test);
            $this->results[$result->getStatus()]++;
            
            //Don't output if we're not running verbosely and the test passes.
            if ($result->getStatus() !== ResultStatus::PASSED || $output->isVerbose()) {
                $output->writeln(
                    ' > ' . $test->getName() . ' : ' .
                    $this->getColouredStatus($result->getStatus()) .
                    ' - ' . $result->getInfo()
                );
            }
            $this->_em->persist($result);
        }

        if ($input->getOption('discard-results')) {
            $output->writeln(' > <comment>Ran with --discard-results, results NOT saved.</comment>');
        } else {
            $this->_em->flush();
        }

        $output->writeln($this->getSummary($start));

        //Exit with status code equal to the number of tests that didn't pass
        return (count($tests) - $this->results[ResultStatus::PASSED]);
    }
    
    private function getColouredStatus($status, $value = null)
    {
        if ($value === null) {
            $value = $status;
        } else {
            $value .= ' ' . $status;
        }
        
        ResultStatus::isValid($status);
        return '<' . $this->colours[$status] . '>' . $value . '</' . $this->colours[$status] . '>';
    }
    
    /**
     * @param \DateTime $start
     */
    private function getSummary(\DateTime $start)
    {
        $end = new \DateTime;
        $runTime = $end->diff($start, true);
        $summary = '';
        
        foreach (ResultStatus::getAll() as $status) {
            $summary .= $this->getColouredStatus($status, $this->results[$status]) . ', ';
        }
        
        $summary .= 'in ' . $runTime->i . ' minutes and ' . $runTime->s . ' seconds';
        return $summary;
    }
}
