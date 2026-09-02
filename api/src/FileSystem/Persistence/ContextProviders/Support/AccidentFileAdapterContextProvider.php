<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Support;

use App\Entity\Support\AccidentFile;
use App\Entity\Support\EquipmentAccident;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class AccidentFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return AccidentFile::class;
    }

    public function getDirectory(): string
    {
        return 'support/accidents';
    }

    public function getFileProperty(): string
    {
        return 'accidentFiles';
    }

    /**
     * @param EquipmentAccident $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'FUR%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            mb_substr(Utf8Slugger::slugify($context['filename']), 0, (int) $context['nameLength']),
            $context['extension']
        );
    }

    protected function getMaxSize(): string
    {
        return '5M';
    }
}
