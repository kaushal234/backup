<?php

declare(strict_types=1);

namespace App\Factory\Quality;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Dto\Quality\CrabSalesOrderLine;
use App\Entity\EquipmentRecord;
use App\Entity\Parts\CrabPart;
use App\Entity\Quality\Crab;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CrabSalesOrderLineFactory
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(CrabSalesOrderLine $crabSalesOrderLine, EquipmentRecord $equipmentRecord): Crab
    {
        $crab = new Crab();
        $part = null;
        if (null !== $crabSalesOrderLine->partNumber && null !== $crabSalesOrderLine->partDescription) {
            $part = new CrabPart();
            $part->partNumber = $crabSalesOrderLine->partNumber;
            $part->description = $crabSalesOrderLine->partDescription;
        }

        $crab->setPart($part);

        $crab->equipmentRecord = $equipmentRecord;
        $crab->description = $crabSalesOrderLine->description;
        $crab->category = $crabSalesOrderLine->category;
        $crab->department = $crabSalesOrderLine->department;
        $crab->nonConformity = $crabSalesOrderLine->nonConformity;
        $crab->code = $crabSalesOrderLine->code;
        $crab->eapId = (int) $crabSalesOrderLine->eapId;

        $violations = $this->validator->validate($crab, null, ['Default', 'crab:create']);

        if (\count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return $crab;
    }
}
