<?php

declare(strict_types=1);

namespace App\FileSystem;

class DrawingExtensionFactory
{
    public static function getExtension(string $signalCode): string
    {
        return \in_array($signalCode, ['PRG', 'PRM'], true) ? 'zip' : 'pdf';
    }
}
