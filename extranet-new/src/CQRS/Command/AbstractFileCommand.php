<?php

declare(strict_types=1);

namespace App\CQRS\Command;

use App\CQRS\Command\User\FileCommandInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractFileCommand implements FileCommandInterface
{
    public function __construct(
        public readonly int $id,
        public readonly UploadedFile $file,
        public readonly ?string $description = null,
    ) {
    }
}
