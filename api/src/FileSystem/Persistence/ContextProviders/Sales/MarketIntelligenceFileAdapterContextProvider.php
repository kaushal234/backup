<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\Sales\MarketIntelligence\MarketIntelligenceFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class MarketIntelligenceFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return MarketIntelligenceFile::class;
    }

    /**
     * @param MarketIntelligence $subject
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
        return 'marketIntelligenceFiles';
    }

    public function getDirectory(): string
    {
        return 'sales/market_intelligences';
    }

    protected function getMimeTypes(): array
    {
        return [...parent::getMimeTypes(), ...['application/zip', 'application/x-zip-compressed', 'video/mp4']];
    }

    protected function getMaxSize(): ?string
    {
        return '25M';
    }
}
