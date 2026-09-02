<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Sdk\Resource\ResourceInterface;

class DummyResource implements ResourceInterface
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    public function getIri(): string
    {
        return 'iri';
    }
}
