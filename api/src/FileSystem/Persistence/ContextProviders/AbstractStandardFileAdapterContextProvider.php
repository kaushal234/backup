<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders;

use EasySlugger\Utf8Slugger;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

abstract class AbstractStandardFileAdapterContextProvider implements FileAdapterContextProviderInterface
{
    public function processFile(File $file, array $metadata = [])
    {
        // do nothing
    }

    public function getFileConstraint($subject): Constraint
    {
        $constraint = new FileConstraint();
        $constraint->mimeTypes = $this->getMimeTypes();
        if (null !== $this->getMaxSize()) {
            $constraint->maxSize = $this->getMaxSize();
        }

        return $constraint;
    }

    public function getFileProperty(): string
    {
        return 'files';
    }

    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%07s-%s-%s.%s',
            method_exists($subject, 'getId') ? $subject->getId() : '',
            null !== $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            mb_substr(Utf8Slugger::slugify($context['filename']), 0, (int) $context['nameLength']),
            $context['extension']
        );
    }

    protected function getMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/octet-stream',
            'image/jpeg',
            'image/png',
            'application/vnd.ms-outlook',
            'application/CDFV2-unknown', // hack for outlook message for now
        ];
    }

    protected function getMaxSize(): ?string
    {
        return '7M';
    }
}
