<?php

declare(strict_types=1);

namespace App\CQRS\Command\Equipment;

use App\CQRS\Command\CommandInterface;
use App\DataTransferObject\UpdateEquipmentRecord;

class UpdateEquipmentCommand implements CommandInterface
{
    public function __construct(
        public UpdateEquipmentRecord $updateEquipmentRecord,
    ) {
    }
}
