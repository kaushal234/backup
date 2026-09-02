<?php

declare(strict_types=1);

namespace Shared\Models\Manufacturing;

class EquipmentRecord
{
    public function __construct(
        public int $id,
        public string $iri,
    ) {
    }
}