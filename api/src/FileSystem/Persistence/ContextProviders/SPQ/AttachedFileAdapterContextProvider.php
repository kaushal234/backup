<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\SPQ;

use App\Entity\SPQ\AttachedFile;
use App\Entity\SPQ\Quotation;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class AttachedFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return AttachedFile::class;
    }

    public function getDirectory(): string
    {
        return 'parts/spq/attached_files';
    }

    public function getFileProperty(): string
    {
        return 'attachedFiles';
    }

    /**
     * @param Quotation $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'SPQ%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            mb_substr(Utf8Slugger::slugify($context['filename']), 0, (int) $context['nameLength']),
            $context['extension']
        );
    }

    protected function getMaxSize(): string
    {
        return '2M';
    }
}
