<?php

declare(strict_types=1);

namespace App\Command\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:esr:cleanup', description: 'Cleans up all ESR older than a week that have no ER')]
class EquipmentShippingRecordCleanupCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $equipmentShippingRecords = $this->entityManager->getRepository(EquipmentShippingRecord::class)->findAll();
        $sevenDaysAgo = (new \DateTime('Today -7 days'))->format('Y-m-d H:i:s');
        $deletedEsrCount = 0;
        $progressBar = new ProgressBar($output, \count($equipmentShippingRecords));

        /** @var EquipmentShippingRecord $equipmentShippingRecord */
        foreach ($equipmentShippingRecords as $equipmentShippingRecord) {
            $progressBar->advance();
            if (
                $equipmentShippingRecord->getEquipmentShippingRecordLines()->isEmpty()
                && $sevenDaysAgo > $equipmentShippingRecord->createdAt->modify('-7 day')->format('Y-m-d H:i:s')
            ) {
                ++$deletedEsrCount;
                $this->entityManager->remove($equipmentShippingRecord);
            }
        }
        $this->entityManager->flush();
        $output->writeln(\sprintf(' %s empty ESR were deleted', $deletedEsrCount));

        return Command::SUCCESS;
    }
}
