<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Engineering\Pictogram;

use App\Entity\Engineering\Pictogram\Pictogram;
use App\Entity\Engineering\Pictogram\PictogramFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class PictogramFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return PictogramFile::class;
    }

    public function getDirectory(): string
    {
        return 'engineering/pictograms';
    }

    public function getGeneratedFilename(object $subject, array $context): string
    {
        /* @var Pictogram $subject */
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
        return '5M';
    }

    protected function getMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/svg',
            'image/gif',
            'application/postscript',
            'application/acad',
            'image/vnd.dwg',
            'image/x-dwg',
            'application/vsn.adobe.illustrator',
        ];
    }
}
