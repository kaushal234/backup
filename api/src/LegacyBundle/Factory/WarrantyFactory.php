<?php

declare(strict_types=1);

namespace LegacyBundle\Factory;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Entity\EquipmentRecord;
use LegacyBundle\Entity\WarrantyClaim;
use Symfony\Bundle\SecurityBundle\Security;

class WarrantyFactory
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly Security $security,
        private readonly EntityManagerInterface $legacyEntityManager,
    ) {
    }

    public function createFromTechnicianOnCall(TechnicianOnCall $technicianOnCall): WarrantyClaim
    {
        $warranty = new WarrantyClaim();

        $warranty->description = $technicianOnCall->description;
        $warranty->unitOperationalStatus = $technicianOnCall->unitOperationalStatus->getName();
        $warranty->status = WarrantyClaim::PENDING;
        $warranty->filteringFlag = WarrantyClaim::TO_BE_FILTERED;

        /** @var People $user */
        $user = $this->security->getUser();
        $warranty->createdBy = $user->getEmail();
        $warranty->createdAt = new \DateTimeImmutable();

        $repository = $this->legacyEntityManager->getRepository(EquipmentRecord::class);
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $repository->find($technicianOnCall->equipmentRecord->getLegacyId());

        $warranty->equipmentRecord = $equipmentRecord;

        $warrantyAcceptedByFactory = $equipmentRecord->salesOrderUnit->salesOrderLine->warrantyAccepted ?? 'N';

        $warranty->customerName = $equipmentRecord->customerName;
        $warranty->location = $equipmentRecord->deliveryLocation;
        $warranty->type = $equipmentRecord->type;
        $warranty->model = $equipmentRecord->model;
        $warranty->factory = $equipmentRecord->factory;
        $warranty->salesOrganisation = $equipmentRecord->salesOrganisation;
        $warranty->serialNumber = $equipmentRecord->serialNumber;
        $warranty->hours = $equipmentRecord->hours;
        $warranty->details = <<<EOF
                SHIPPED:{$equipmentRecord->shippedDate->format('Y-m-d')}, WARRANTY LEN:{$equipmentRecord->warrantyLength},
                WARRANTY END:{$equipmentRecord->dateWarrantyEnd->format('Y-m-d')}\n
                <b>Special Warranty Conditions:</b>
                {$equipmentRecord->warrantyConditions}
                <b>Warranty Conditions accepted by factory? </b>$warrantyAcceptedByFactory
            EOF;

        $this->validator->validate($warranty);

        return $warranty;
    }
}
