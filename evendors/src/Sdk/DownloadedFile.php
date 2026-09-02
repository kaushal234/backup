<?php

declare(strict_types=1);

namespace App\Sdk;

use Psl\File\ReadHandleInterface;

final class DownloadedFile
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public readonly int $status,
        public readonly array $headers,
        public readonly ReadHandleInterface $handle,
    ) {
    }
}
