<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Service;

use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallMainFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class TechnicianOnCallMainFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return TechnicianOnCallMainFile::class;
    }

    public function getFileProperty(): string
    {
        return 'mainFile';
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
        return ['image/jpeg', 'image/png'];
    }
}
