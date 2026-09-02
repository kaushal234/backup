<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ImportBatchHelper
{
    public function runBatch(InputInterface $input, OutputInterface $output, string $command, int $numberOfRows, ProcessHelper $processHelper, int $timeout = 600, int $batchSize = 100_000)
    {
        $blocksCount = (int) ceil($numberOfRows / $batchSize);
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        for ($i = 0; $i < $blocksCount; ++$i) {
            $start = $i * $batchSize;
            $output->writeln(\sprintf('<info>Running %s imports starting from %s</info>', $batchSize, $start));
            $process = new Process(['bin/console', $command, $start, $batchSize, '--env', $input->getOption('env')], null, null, null, $timeout);
            $processHelper->mustRun($output, $process, null, static function ($type, $buffer) use ($output) {
                $output->writeln($buffer);
            });
        }
    }

    public static function addArguments(Command $command)
    {
        $command
            ->addArgument('offset', InputArgument::REQUIRED, 'offset to start the import')
            ->addArgument('limit', InputArgument::REQUIRED, 'number of rows to import at a time')
        ;
    }
}
