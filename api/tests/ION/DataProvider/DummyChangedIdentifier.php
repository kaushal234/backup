<?php

declare(strict_types=1);

namespace App\Tests\ION\DataProvider;

use App\ION\Resources\ItemProviderIdentifierInterface;

class DummyChangedIdentifier implements ItemProviderIdentifierInterface
{
    public static function getIdentifier(array $identifier): array
    {
        return ['id' => 'changed identifier'];
    }
}
