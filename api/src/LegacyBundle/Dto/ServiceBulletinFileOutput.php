<?php

declare(strict_types=1);

namespace App\LegacyBundle\Dto;

final class ServiceBulletinFileOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $filePath,
        public readonly ?string $description,
        public readonly string $createdAt,
        public readonly ?string $extension,
        public readonly ?int $size,
        public readonly string $filename,
    ) {
    }
}
