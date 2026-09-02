<?php

declare(strict_types=1);

namespace App\ION\DataProcessor\Manufacturing;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\EquipmentRecord;
use App\Manager\EquipmentRecordManager;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use App\Repository\EquipmentRecordRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template T
 */
class EstimatedGreenTagDateEquipmentRecordDataProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EquipmentRecordRepository $equipmentRecordRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EquipmentRecordNotifier $equipmentRecordNotifier,
        private readonly EquipmentRecordManager $equipmentRecordManager
    ) {
    }

    /**
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var EquipmentRecord|null $equipmentRecord */
        $equipmentRecord = $this->equipmentRecordRepository->findOneBy(['projectNumber' => $data->equipmentRecord]);

        if (null === $equipmentRecord || null !== $equipmentRecord->getGreenTagDate()) {
            return $data;
        }
        $previousEstimatedGreenTagDate = $equipmentRecord->getEstimatedGreenTagDate();

        if ($previousEstimatedGreenTagDate?->format('Y-m-d') === $data->estimatedGreenTagDate) {
            return $data;
        }
        $equipmentRecord->setEstimatedGreenTagDate(new \DateTime($data->estimatedGreenTagDate));

        $this->entityManager->persist($equipmentRecord);
        $this->entityManager->flush();

        if ($this->equipmentRecordManager->updatedEstimatedGreenTagDateNeedsToSendGapAlert($equipmentRecord, $previousEstimatedGreenTagDate)) {
            $this->equipmentRecordNotifier->sendAlertEstimatedGreenTagDateGap($equipmentRecord, $previousEstimatedGreenTagDate->format('Y-m-d'));
        }

        return $data;
    }
}
