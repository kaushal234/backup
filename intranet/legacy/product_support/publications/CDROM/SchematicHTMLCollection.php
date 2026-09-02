<?php
declare(strict_types=1);

class SchematicHTMLCollection
{
    public function generate(array $schematicsCollection): array
    {
        $htmlCollection = [];

        foreach ($schematicsCollection as $schematic) {
            $htmlCollection[] = [
                'label' => $schematic['component'] . $schematic['serial'],
                'file' => basename($schematic['file'])];
        }

        return $htmlCollection;
    }
}