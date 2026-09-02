<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

enum UnitOperationalStatusChoices
{
    case MCF;
    case MCP;
    case NMC;

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
