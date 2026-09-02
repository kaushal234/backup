<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Service;

use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class TechnicianOnCallFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return TechnicianOnCallFile::class;
    }

    public function getFileProperty(): string
    {
        return 'files';
    }

    public function getDirectory(): string
    {
        return 'service/technician_on_call';
    }

    /**
     * @param TechnicianOnCall $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            Utf8Slugger::slugify(pathinfo($context['filename'], \PATHINFO_FILENAME)),
            $context['extension']
        );
    }

    protected function getMaxSize(): ?string
    {
        return '25M';
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint', 'video/mp4']];
    }
}
