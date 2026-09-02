<?php

declare(strict_types=1);

namespace App\Link\ResourceSourceProvider;

abstract class AbstractResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getProperties(): string
    {
        return 'id name';
    }

    public function getReturnedFields(): string
    {
        return 'id name';
    }
}
