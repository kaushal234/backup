<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Purchasing;

use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Entity\Purchasing\SupplierRanking\SupplierRankingFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class SupplierRankingFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return SupplierRankingFile::class;
    }

    public function getDirectory(): string
    {
        return 'purchasing/supplier_ranking';
    }

    /**
     * @param SupplierRanking $subject
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
        return '15M';
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint', 'video/mp4']];
    }
}
