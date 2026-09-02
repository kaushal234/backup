<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Quality;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class FirstArticleQualificationFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return FirstArticleQualificationFile::class;
    }

    public function getDirectory(): string
    {
        return 'quality/first_article_qualification';
    }

    /**
     * @param FirstArticleQualification $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'FAQ%07s-%s-%s.%s',
            $subject->getId(),
            $context['createdAt'] ? $context['createdAt']->format('YmdHis') : date('YmdHis'),
            mb_substr(Utf8Slugger::slugify($context['filename']), 0, (int) $context['nameLength']),
            $context['extension']
        );
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed', 'message/rfc822']];
    }

    protected function getMaxSize(): string
    {
        return '10M';
    }
}
