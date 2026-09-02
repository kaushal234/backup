<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

interface ResourceInterface
{
    public function getIri(): string;
}
