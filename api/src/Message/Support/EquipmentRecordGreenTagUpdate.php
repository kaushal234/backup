<?php

declare(strict_types=1);

namespace App\Message\Support;

class EquipmentRecordGreenTagUpdate
{
    private readonly string $equipmentRecordIri;
    private readonly ?string $previousGreenTagDate;
    private readonly string $userIri;

    public function __construct(string $equipmentRecordIri, ?string $previousGreenTagDate, string $userIri)
    {
        $this->equipmentRecordIri = $equipmentRecordIri;
        $this->previousGreenTagDate = $previousGreenTagDate;
        $this->userIri = $userIri;
    }

    public function getEquipmentRecordIri(): string
    {
        return $this->equipmentRecordIri;
    }

    public function getPreviousGreenTagDate(): ?string
    {
        return $this->previousGreenTagDate;
    }

    public function getUserIri(): string
    {
        return $this->userIri;
    }
}
