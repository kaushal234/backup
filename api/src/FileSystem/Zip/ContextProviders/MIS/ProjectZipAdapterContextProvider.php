<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders\MIS;

use App\Entity\MIS\Project\Project;
use App\FileSystem\Zip\ContextProviders\AbstractZippableEntityAdapterContextProvider;

class ProjectZipAdapterContextProvider extends AbstractZippableEntityAdapterContextProvider
{
    public static function getClass(): string
    {
        return Project::class;
    }

    /** @param Project $subject */
    public function getArchiveName(object $subject): string
    {
        return \sprintf('Project-%d', $subject->getId());
    }
}
