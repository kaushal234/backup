<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Quality;

use App\Entity\Quality\CalibratedTools\CalibrationLog;
use App\Entity\Quality\CalibratedTools\CalibrationLogFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class CalibrationLogFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return CalibrationLogFile::class;
    }

    public function getFileProperty(): string
    {
        return 'certificate';
    }

    public function getDirectory(): string
    {
        return 'quality/tools/certificates';
    }

    /**
     * @param CalibrationLog $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%s_%s_%s.%s',
            (new \DateTime())->format('Y-m-d'),
            Utf8Slugger::slugify($subject->getTool()->getLocationArea()->getName()),
            Utf8Slugger::uniqueSlugify($subject->getTool()->getToolType()->getDescription()),
            $context['extension']
        );
    }

    protected function getMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }

    protected function getMaxSize(): string
    {
        return '5M';
    }
}
