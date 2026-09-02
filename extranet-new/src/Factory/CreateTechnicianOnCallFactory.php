<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataTransferObject\TechnicianOnCall\Airport;
use App\DataTransferObject\TechnicianOnCall\CreateTechnicianOnCall;
use App\DataTransferObject\TechnicianOnCall\Equipment;
use App\Sdk\Resource\EquipmentRecord;

class CreateTechnicianOnCallFactory
{
    public function createFromEquipmentRecord(?EquipmentRecord $equipmentRecord = null): CreateTechnicianOnCall
    {
        $technicianOnCallForm = new CreateTechnicianOnCall();

        if (!$equipmentRecord instanceof EquipmentRecord) {
            return $technicianOnCallForm;
        }

        if ($equipmentRecord->airport) {
            $airport = new Airport();
            $airport->iri = $equipmentRecord->airport->iri;
            $technicianOnCallForm->airport = $airport;
        }

        $equipmentRecordDto = new Equipment();
        $equipmentRecordDto->iri = $equipmentRecord->iri;

        $technicianOnCallForm->equipment = $equipmentRecordDto;
        $technicianOnCallForm->hourMeter = $equipmentRecord->hourMeter;

        return $technicianOnCallForm;
    }
}
