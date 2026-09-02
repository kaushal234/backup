<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

/**
 * A marker interface for all resource objects.
 */
interface ResourceInterface
{
    public function getIri(): string;
}
