<?php
declare(strict_types=1);

require_once 'SchematicFileProvider.php';

class SchematicsFileCollection
{
    private $manualDirectory;
    private $equipment;
    private $schematics = [];

    public function __construct(tldEquipment $equipment, SchematicsTmpDirectory $manualDirectory)
    {
        $this->manualDirectory = $manualDirectory;
        $this->equipment = $equipment;
    }

    public function init(): void
    {
        foreach ($this->equipment->getSchematics() as $schematic) {
            if (empty($schematic['serial']) || empty($schematic['brand'])) {
                continue;
            }

            $schematicProvider = new SchematicFileProvider($this->manualDirectory);
            if (false === $path = $schematicProvider->get($schematic, $this->equipment)) {
                continue;
            }

            $this->schematics[] = $schematic + ['file' => $path];
        }
    }

    public function getSchematics(): array
    {
        return $this->schematics;
    }
}