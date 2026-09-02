<?php

declare(strict_types=1);

namespace App\Message\Finance;

class AccountReceivableImportFromFile
{
    public string $filepath;
    public string $to;
    public string $locationIri;

    public function __construct(string $filepath, string $to, string $locationIri)
    {
        $this->filepath = $filepath;
        $this->to = $to;
        $this->locationIri = $locationIri;
    }
}
