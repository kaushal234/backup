<?php

declare(strict_types=1);

namespace App\Message\Support;

class ManualDuplication
{
    private readonly string $resourceIri;
    private readonly array $secondaryEquipmentRecords;
    private readonly array $schematicsSerialsIri;
    private readonly string $userIri;

    public function __construct(string $resourceIri, array $secondaryEquipmentRecords, array $schematicsSerialsIri, string $userIri)
    {
        $this->resourceIri = $resourceIri;
        $this->secondaryEquipmentRecords = $secondaryEquipmentRecords;
        $this->schematicsSerialsIri = $schematicsSerialsIri;
        $this->userIri = $userIri;
    }

    public function getResourceIri(): string
    {
        return $this->resourceIri;
    }

    public function getSecondaryEquipmentRecords(): array
    {
        return $this->secondaryEquipmentRecords;
    }

    public function getSchematicsSerialsIri(): array
    {
        return $this->schematicsSerialsIri;
    }

    public function getUserIri(): string
    {
        return $this->userIri;
    }
}
