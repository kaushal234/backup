<?php

declare(strict_types=1);

namespace Shared\Models\Service;

class TldOnCall
{
    public function __construct(
        public int $id,
        public string $iri,
    ) {
    }
}