<?php

declare(strict_types=1);

namespace App\AI\Platform;

readonly class PlatformResult
{
    public function __construct(
        public string $result,
        public ?string $logIri = null,
    ) {
    }
}
