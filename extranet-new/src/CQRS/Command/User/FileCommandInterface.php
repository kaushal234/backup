<?php

declare(strict_types=1);

namespace App\CQRS\Command\User;

use App\CQRS\Command\CommandInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileCommandInterface extends CommandInterface
{
    public int $id { get; }

    public UploadedFile $file { get; }

    public ?string $description { get; }
}
