<?php

declare(strict_types=1);

namespace App\Manager\Sales;

class EquipmentShippingRecordLineManager
{
    public function getPickUpInformationChanges(?\DateTimeInterface $oldDate, ?\DateTimeInterface $newDate, ?bool $oldConfirmation, ?bool $newConfirmation): array
    {
        $changes = [];

        $oldDateFormatted = $oldDate?->format('Y-m-d');
        $newDateFormatted = $newDate?->format('Y-m-d');

        if ($oldDateFormatted !== $newDateFormatted) {
            $changes['estimatedPickUpDate'] = [
                'old' => $oldDate,
                'new' => $newDate,
            ];
        }

        if ($oldConfirmation !== $newConfirmation) {
            $changes['estimatedPickUpDateConfirmation'] = [
                'old' => $oldConfirmation,
                'new' => $newConfirmation,
            ];
        }

        return $changes;
    }
}
