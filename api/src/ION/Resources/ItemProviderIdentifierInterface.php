<?php

declare(strict_types=1);

namespace App\ION\Resources;

interface ItemProviderIdentifierInterface
{
    public static function getIdentifier(array $identifier): array;
}
