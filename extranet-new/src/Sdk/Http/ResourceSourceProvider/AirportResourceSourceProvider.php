<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Resource\Airport;

class AirportResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return Airport::class === $resource;
    }

    public function getResourceIri(): string
    {
        return 'airports';
    }
}
