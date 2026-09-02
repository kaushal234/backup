<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Activity;

use App\Entity\Activity\CommentFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class CommentFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return CommentFile::class;
    }

    public function getDirectory(): string
    {
        return 'comments';
    }

    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%s-%s.%s',
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            Utf8Slugger::slugify($context['filename']),
            $context['extension']
        );
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint', 'video/mp4']];
    }

    protected function getMaxSize(): ?string
    {
        return '25M';
    }
}
