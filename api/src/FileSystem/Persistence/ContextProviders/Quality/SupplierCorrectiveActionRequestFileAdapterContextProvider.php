<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Quality;

use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Entity\Quality\SupplierCorrectiveActionRequestFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class SupplierCorrectiveActionRequestFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return SupplierCorrectiveActionRequestFile::class;
    }

    public function getDirectory(): string
    {
        return 'quality/scar';
    }

    /**
     * @param SupplierCorrectiveActionRequest $subject
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
        return '25M';
    }

    protected function getMimeTypes(): array
    {
        return [
            ...parent::getMimeTypes(),
            ...[
                'application/zip',
                'application/x-zip-compressed',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-powerpoint',
                'video/mp4',
                'application/x-rar',
                'application/msword',
                'text/html',
                'video/3gpp',
                'image/tiff',
                'text/plain',
                'video/x-msvideo',
                'video/quicktime',
                'image/x-ms-bmp',
                'application/CDFV2',
                'application/x-shockwave-flash',
                'application/x-7z-compressed',
                'application/CDFV2-corrupt',
            ],
        ];
    }
}
