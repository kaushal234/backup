<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Quality;

use App\Entity\Quality\CalibratedTools\OutOfToleranceForm;
use App\Entity\Quality\CalibratedTools\OutOfToleranceFormFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class OutOfToleranceFormFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return OutOfToleranceFormFile::class;
    }

    public function getDirectory(): string
    {
        return 'quality/tools/out_of_tolerance_forms';
    }

    /**
     * @param OutOfToleranceForm $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'otf_%d_%s_%s.%s',
            $subject->getId(),
            (new \DateTime())->format('Y-m-d'),
            Utf8Slugger::uniqueSlugify($subject->getCalibrationLog()->getTool()->getToolType()->getDescription()),
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
