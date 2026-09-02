<?php

declare(strict_types=1);

namespace App\Message\Finance;

class AccountReceivableDelinquent
{
    private readonly ?string $locationIri;

    public function __construct(?string $locationIri = null)
    {
        $this->locationIri = $locationIri;
    }

    public function getLocationIri(): ?string
    {
        return $this->locationIri;
    }
}
