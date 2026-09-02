<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Parts;

use App\Entity\Parts\TransportationNote;
use App\Entity\Parts\TransportationNoteFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class TransportationNoteFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return TransportationNoteFile::class;
    }

    public function getDirectory(): string
    {
        return 'parts/transportation_notes';
    }

    /**
     * @param TransportationNote $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'TN%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            mb_substr(Utf8Slugger::slugify($context['filename']), 0, (int) $context['nameLength']),
            $context['extension']
        );
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed']];
    }
}
