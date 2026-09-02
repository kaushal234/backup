<?php

declare(strict_types=1);

namespace Shared\Models\Service;

class ServiceBulletinLines
{
    public function __construct(
        public int $id,
        public string $iri,
    ) {
    }
}