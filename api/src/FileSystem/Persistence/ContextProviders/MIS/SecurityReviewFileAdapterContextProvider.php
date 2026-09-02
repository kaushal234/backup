<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\MIS;

use App\Entity\Module\ThirdPartyApp\SecurityReviewFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class SecurityReviewFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return SecurityReviewFile::class;
    }

    public function getDirectory(): string
    {
        return 'mis/modules/third_party_app/security_review';
    }

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
        return '15M';
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint', 'video/mp4', 'video/webm', 'video/x-matroska']];
    }
}
