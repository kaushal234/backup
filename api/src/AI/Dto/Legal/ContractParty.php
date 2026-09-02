<?php

declare(strict_types=1);

namespace App\AI\Dto\Legal;

final readonly class ContractParty
{
    public function __construct(
        public string $partyName,
        public string $partyRole,
    ) {
    }
}
