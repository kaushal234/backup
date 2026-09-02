<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders;

use App\FileSystem\AdapterContextProviderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;

interface FileAdapterContextProviderInterface extends AdapterContextProviderInterface
{
    public function processFile(File $file, array $metadata = []);

    public function getFileConstraint($subject): ?Constraint;

    public function getFileProperty(): string;

    public function getDirectory(): string;

    public function getGeneratedFilename(object $subject, array $context): string;
}
