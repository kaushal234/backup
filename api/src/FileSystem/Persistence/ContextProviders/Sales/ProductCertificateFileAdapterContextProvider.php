<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Sales;

use App\Entity\Sales\ProductCertificate;
use App\Entity\Sales\ProductCertificateFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class ProductCertificateFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return ProductCertificateFile::class;
    }

    public function getDirectory(): string
    {
        return 'sales/catalogue';
    }

    /**
     * @param ProductCertificate $subject
     */
    public function getGeneratedFilename(object $subject, array $context): string
    {
        return \sprintf(
            'CAAC%07s-%s-%s.%s',
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
