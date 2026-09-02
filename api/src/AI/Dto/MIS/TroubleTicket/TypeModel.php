<?php

declare(strict_types=1);

namespace App\AI\Dto\MIS\TroubleTicket;

final readonly class TypeModel
{
    public function __construct(
        public string $type,
        public string $description,
        public ?string $indiceFactor,
    ) {
    }
}
