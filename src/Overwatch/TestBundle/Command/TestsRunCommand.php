<?php

namespace Overwatch\TestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestsRunCommand extends ContainerAwareCommand {
    /**
     * @var Overwatch\ExpectationBundle\Helper\ExpectationManger 
     */
    private $expectations;
    
    public function setContainer(ContainerInterface $container = NULL) {
        parent::setContainer($container);
        $this->expectations = $this->getContainer()->get("overwatch_expectation.expectation_manager");
    }
    
    protected function configure() {
        $this
            ->setName('overwatch:tests-run')
            ->setDescription('Run a set of overwatch tests')
            #->addOption('test', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'If set, the task will only run the tests given')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        echo var_dump($this->expectations->run("8.8.8.8", "toPing"));
        echo var_dump($this->expectations->run("prod10.second2digital.com", "toResolveTo", "46.137.88.242"));
        echo var_dump($this->expectations->run("prod11.second2digital.com", "toResolveTo", "79.125.8.86"));
    }
}
