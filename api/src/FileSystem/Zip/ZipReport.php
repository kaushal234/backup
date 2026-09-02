<?php

declare(strict_types=1);

namespace App\FileSystem\Zip;

final class ZipReport
{
    public int $requested = 0;
    public int $included = 0;

    /** @var array<int, array{path: string, reason: string}> */
    public array $excluded = [];

    public function exclude(string $path, string $reason): void
    {
        $this->excluded[] = [
            'path' => $path,
            'reason' => $reason,
        ];
    }
}
