<?php

declare(strict_types=1);

namespace App\Command\Support;

use App\Link\Manager\Support\EquipmentRecordManager;
use App\Repository\EquipmentRecordRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:equipment:link:synchronization')]
class EquipmentRecordLinkSynchronizationCommand extends Command
{
    public function __construct(
        private readonly EquipmentRecordManager $manager,
        private readonly EquipmentRecordRepository $equipmentRecordRepository,
    ) {
        parent::__construct();
        $this->setDescription('Synchronize ER with LINK platform');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->manager->synchronizeEquipmentRecord($this->equipmentRecordRepository->findEquipmentToSynchronizeWithLink());
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
