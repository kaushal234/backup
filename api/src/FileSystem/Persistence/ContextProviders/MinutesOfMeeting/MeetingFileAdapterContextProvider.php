<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\MinutesOfMeeting;

use App\Entity\MinutesOfMeeting\Meeting;
use App\Entity\MinutesOfMeeting\MeetingFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class MeetingFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return MeetingFile::class;
    }

    public function getDirectory(): string
    {
        return 'minutes_of_meeting/meetings';
    }

    /**
     * @param Meeting $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'MinutesOfMeeting%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            mb_substr(Utf8Slugger::slugify($context['filename']), 0, (int) $context['nameLength']),
            $context['extension']
        );
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint']];
    }

    protected function getMaxSize(): ?string
    {
        return '25M';
    }
}
