<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Legal;

use App\Entity\Legal\Contract;
use App\Entity\Legal\ContractFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class ContractFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return ContractFile::class;
    }

    public function getDirectory(): string
    {
        return 'legal';
    }

    /**
     * @param Contract $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            Utf8Slugger::slugify($context['filename']),
            $context['extension']
        );
    }

    protected function getMaxSize(): ?string
    {
        return '32M';
    }

    protected function getMimeTypes(): array
    {
        return [
            'application/zip',
            'application/x-zip-compressed',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint',
            'video/mp4',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }
}
