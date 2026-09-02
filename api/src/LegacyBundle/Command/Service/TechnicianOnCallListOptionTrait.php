<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @mixin Command
 */
trait TechnicianOnCallListOptionTrait
{
    protected string $tocIdList = '';

    protected function configure(): void
    {
        $this
            ->addOption('tocIds', 'tocIds', InputOption::VALUE_REQUIRED, 'List of TOC IDs to import, separate by a comma')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $tocIdList = $input->getOption('tocIds');

        if (!$tocIdList) {
            throw new \InvalidArgumentException('No TOC ID provided');
        }

        if (!preg_match('/^\d+(,\d+)*$/', $tocIdList)) {
            throw new \InvalidArgumentException('--tocIds doit contenir uniquement des nombres séparés par des virgules.');
        }

        $this->tocIdList = $tocIdList;
    }
}
