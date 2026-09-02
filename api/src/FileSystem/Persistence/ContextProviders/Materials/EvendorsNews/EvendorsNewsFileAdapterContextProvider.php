<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Materials\EvendorsNews;

use App\Entity\Materials\EvendorsNews;
use App\Entity\Materials\EvendorsNewsFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class EvendorsNewsFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getFileClass(): string
    {
        return EvendorsNewsFile::class;
    }

    public function getDirectory(): string
    {
        return 'evendors_news';
    }

    /**
     * @param EvendorsNews $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'Evendors_news-%s-file-%s-%s/%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            mb_substr(Utf8Slugger::slugify($context['filename']), 0, (int) $context['nameLength']),
            $context['filename']
        );
    }

    public static function getClass(): string
    {
        return EvendorsNewsFile::class;
    }

    protected function getMimeTypes(): array
    {
        return [
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.ms-powerpoint',
        ];
    }
}
