<?php

declare(strict_types=1);

namespace App\Message\Sales;

class CustomerMainRepresentativeUpdate
{
    private readonly string $resourceIri;
    private readonly string $asmIri;

    public function __construct(string $resourceIri, string $asmIri)
    {
        $this->resourceIri = $resourceIri;
        $this->asmIri = $asmIri;
    }

    public function getResourceIri(): string
    {
        return $this->resourceIri;
    }

    public function getAsmIri(): string
    {
        return $this->asmIri;
    }
}
