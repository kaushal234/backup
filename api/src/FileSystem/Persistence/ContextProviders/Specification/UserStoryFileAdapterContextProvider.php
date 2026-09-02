<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Specification;

use App\Entity\Module\Specification\UserStory;
use App\Entity\Module\Specification\UserStoryFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class UserStoryFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return UserStoryFile::class;
    }

    /**
     * @param UserStory $subject
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

    public function getFileProperty(): string
    {
        return 'userStoryFiles';
    }

    public function getDirectory(): string
    {
        return 'mis/user_stories';
    }

    protected function getMimeTypes(): array
    {
        return ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    }

    protected function getMaxSize(): ?string
    {
        return '25M';
    }
}
