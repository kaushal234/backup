<?php

declare(strict_types=1);

namespace App\Command\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\Incoterm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:esr:status-update', description: 'Updates not ended ESR status to CLOSED or SHIPPED if all their ERs are shipped')]
class EquipmentShippingRecordStatusUpdateCommand extends Command
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
        $equipmentShippingRecords = $this->entityManager->getRepository(EquipmentShippingRecord::class)
            ->findBy(['status' => [EquipmentShippingRecord::PENDING, EquipmentShippingRecord::BOOKED]]);
        $updatedEsrCount = 0;
        $progressBar = new ProgressBar($output, \count($equipmentShippingRecords));

        /** @var EquipmentShippingRecord $equipmentShippingRecord */
        foreach ($equipmentShippingRecords as $equipmentShippingRecord) {
            $progressBar->advance();
            if ($this->equipmentShippingRecordShouldBeClosed($equipmentShippingRecord)) {
                $status = \in_array($equipmentShippingRecord->incoterm->code, [Incoterm::EXW, Incoterm::FCA], true) ?
                    EquipmentShippingRecord::CLOSED :
                    EquipmentShippingRecord::SHIPPED;
                $equipmentShippingRecord->setStatus($status);
                ++$updatedEsrCount;
            }
        }
        $this->entityManager->flush();
        $output->writeln(\sprintf(' The status of %s ESR were updated because all their ERs were shipped', $updatedEsrCount));

        return Command::SUCCESS;
    }

    private function equipmentShippingRecordShouldBeClosed(EquipmentShippingRecord $equipmentShippingRecord): bool
    {
        if ($equipmentShippingRecord->getEquipmentShippingRecordLines()->isEmpty()) {
            return false;
        }

        foreach ($equipmentShippingRecord->getEquipmentShippingRecordLines() as $equipmentShippingRecordLine) {
            if (null === $equipmentShippingRecordLine->equipmentRecord->getDateShipped()) {
                return false;
            }
        }

        return true;
    }
}
