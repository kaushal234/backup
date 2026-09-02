<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\AI;

use App\Entity\AI\AIFile;
use App\Entity\AI\Request;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class AIFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return AIFile::class;
    }

    public function getDirectory(): string
    {
        return 'ai';
    }

    public function getFileProperty(): string
    {
        return 'file';
    }

    /**
     * @param Request $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            '%s.%s',
            Utf8Slugger::uniqueSlugify((string) $subject->getId()),
            $context['extension']
        );
    }
}
