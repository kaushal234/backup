<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\SPQ;

use App\Entity\SPQ\Quotation;
use App\Entity\SPQ\QuotationFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class QuotationFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return QuotationFile::class;
    }

    public function getDirectory(): string
    {
        return 'parts/spq/quotations';
    }

    public function getFileProperty(): string
    {
        return 'quotationFiles';
    }

    /**
     * @param Quotation $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'SPQ%07s-%s.pdf',
            $subject->getId(),
            (new \DateTime())->format('YmdHis')
        );
    }

    protected function getMimeTypes(): array
    {
        return ['application/pdf'];
    }

    protected function getMaxSize(): ?string
    {
        return null;
    }
}
