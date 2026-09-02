<?php

declare(strict_types=1);

namespace App\Command\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:equipment-shipping-record:close-obsolete-open-esr',
    description: 'Close open Equipment Shipping Records when all their Equipment Records already belong to a more recent open ESR.',
)]
class CloseObsoleteEquipmentShippingRecordsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $esrRepository = $this->entityManager->getRepository(EquipmentShippingRecord::class);

        /** @var EquipmentShippingRecord[] $openEsrList */
        $openEsrList = $esrRepository->createQueryBuilder('esr')
            ->andWhere('esr.status != :closed')
            ->setParameter('closed', EquipmentShippingRecord::CLOSED)
            ->orderBy('esr.id', 'ASC')
            ->getQuery()
            ->getResult();

        $closedCount = 0;
        $checkedCount = 0;

        foreach ($openEsrList as $equipmentShippingRecord) {
            ++$checkedCount;

            $lines = $equipmentShippingRecord->getEquipmentShippingRecordLines();

            if (0 === $lines->count()) {
                $io->comment(\sprintf(
                    'Skipping ESR #%d: no line found.',
                    $equipmentShippingRecord->getId()
                ));
                continue;
            }

            $allEquipmentRecordsCoveredByNewerOpenEsr = true;

            /** @var EquipmentShippingRecordLine $line */
            foreach ($lines as $line) {
                $equipmentRecord = $line->equipmentRecord;

                $hasNewerOpenEsr = (bool) $this->entityManager->createQueryBuilder()
                    ->select('COUNT(newerLine.id)')
                    ->from(EquipmentShippingRecordLine::class, 'newerLine')
                    ->join('newerLine.equipmentShippingRecord', 'newerEsr')
                    ->andWhere('newerLine.equipmentRecord = :equipmentRecord')
                    ->andWhere('newerEsr != :currentEsr')
                    ->andWhere('newerEsr.status != :closed')
                    ->andWhere('newerEsr.id > :currentEsrId')
                    ->setParameter('equipmentRecord', $equipmentRecord)
                    ->setParameter('currentEsr', $equipmentShippingRecord)
                    ->setParameter('currentEsrId', $equipmentShippingRecord->getId())
                    ->setParameter('closed', EquipmentShippingRecord::CLOSED)
                    ->getQuery()
                    ->getSingleScalarResult();

                if (!$hasNewerOpenEsr) {
                    $allEquipmentRecordsCoveredByNewerOpenEsr = false;
                    break;
                }
            }

            if (!$allEquipmentRecordsCoveredByNewerOpenEsr) {
                continue;
            }

            $equipmentShippingRecord->setStatus(EquipmentShippingRecord::CLOSED);
            ++$closedCount;

            $io->success(\sprintf(
                'ESR #%d closed.',
                $equipmentShippingRecord->getId()
            ));
        }

        if ($closedCount > 0) {
            $this->entityManager->flush();
        }

        $io->section('Summary');
        $io->listing([
            \sprintf('Checked ESR: %d', $checkedCount),
            \sprintf('Closed ESR: %d', $closedCount),
        ]);

        return Command::SUCCESS;
    }
}
