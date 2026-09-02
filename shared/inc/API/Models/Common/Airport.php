<?php

declare(strict_types=1);

namespace Shared\Models\Common;

class Airport
{
    public function __construct(
        public int $id,
        public string $iri,
    ) {
    }
}