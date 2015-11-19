<?php

namespace Overwatch\ResultBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\ResultBundle\Enum\ResultStatus;

/**
 * ResultCleanupCommand
 * The overwatch:result:cleanup command, that archives old results
 */
class ResultCleanupCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Overwatch\ResultBundle\Entity\TestResultRepository
     */
    private $resultRepo;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    private $archiveFile = null;

    protected function configure()
    {
        $this
            ->setName('overwatch:results:cleanup')
            ->setDescription('Deletes and optionally archives older test results')
            ->addOption('delete', null, InputOption::VALUE_REQUIRED, 'Delete test results older than this value')
            ->addOption('compress', null, InputOption::VALUE_REQUIRED, 'Compress test results older than this value')
            ->addOption('archive', null, InputOption::VALUE_NONE, 'Archive items to a file')
            ->setHelp(<<<EOF
The <info>%command.name%</info> deletes and optionally archives older test results.

  <info>php %command.full_name%</info>

By default, the command will perform no operation. You should pass the <info>--delete</info> and/or <info>--compress</info>
options to configure what operations the command will apply.

See https://github.com/zsturgess/overwatch/blob/master/app/Resources/docs/installing.md#cleaning-up-results

EOF
            )
        ;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        //Set up some shortcuts to services
        if ($container !== null) {
            $this->em = $container->get('doctrine.orm.entity_manager');
            $this->resultRepo = $this->em->getRepository('OverwatchResultBundle:TestResult');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $delete = $this->convertOption($input->getOption('delete'));
        $compress = $this->convertOption($input->getOption('compress'));

        $this->output->writeln($this->getApplication()->getLongVersion() . ', running a cleanup');

        if ($delete === null && $compress === null) {
            throw new \InvalidArgumentException('Neither the --delete or --compress options were passed. No operation will be completed.');
        }

        if ($input->getOption('archive')) {
            $this->prepareArchive();
        }

        $this->deleteResults($delete);
        $this->compressResults($compress);

        $output->writeln(' > Applying <info>' . count($this->em->getUnitOfWork()->getScheduledEntityDeletions()) . '</info> cleanup operations');
        $this->em->flush();
        $output->writeln('<info>Cleanup finished</info>');
    }

    private function convertOption($option)
    {
        if ($option === null) {
            return null;
        }

        return new \DateTime($option);
    }

    private function prepareArchive()
    {
        $archiveDir = $this->getContainer()->get('kernel')->getRootDir() . '/logs/';
        $header = $this->getApplication()->getName() . ' ' . $this->getApplication()->getVersion() . ', running a cleanup';

        $this->archiveFile = $archiveDir . 'overwatch_archive_' . date('YmdHis') . '.log';
        $this->output->writeln(' > Preparing archive file <info>' . $this->archiveFile . '</info>');

        if (!file_put_contents($this->archiveFile, $header . PHP_EOL, FILE_APPEND)) {
            throw new \InvalidArgumentException('Could not write to the archive file.');
        }
    }

    /**
     * @param null|\DateTime $delete
     */
    private function deleteResults($delete)
    {
        if ($delete === null) {
            return;
        }

        $this->output->writeln(' > Finding results older than <info>' . $delete->format('Y-m-d H:i:s') . '</info> to delete');
        $resultsToCleanup = $this->resultRepo->getResultsOlderThan($delete);

        foreach ($resultsToCleanup as $row) {
            $result = $row[0];

            $this->archiveResult($result);
        }
    }

    /**
     * @param null|\DateTime $compress
     */
    private function compressResults($compress)
    {
        if ($compress === null) {
            return;
        }

        $lastResult = ResultStatus::PASSED;
        $this->output->writeln(' > Finding results older than <info>' . $compress->format('Y-m-d H:i:s') . '</info> to compress');
        $resultsToCleanup = $this->resultRepo->getResultsOlderThan($compress);

        foreach ($resultsToCleanup as $row) {
            $result = $row[0];

            if ($result->getStatus() === $lastResult) {
                $this->archiveResult($result);
            }

            $lastResult = $result->getStatus();
        }
    }

    private function archiveResult(TestResult $result)
    {
        if ($this->archiveFile !== null) {
            file_put_contents($this->archiveFile, (string) $result . PHP_EOL, FILE_APPEND);
        }

        $this->em->remove($result);
    }
}
