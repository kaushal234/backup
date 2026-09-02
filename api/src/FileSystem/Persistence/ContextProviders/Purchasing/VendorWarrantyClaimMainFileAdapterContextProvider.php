<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Purchasing;

use App\Entity\Purchasing\VendorWarrantyClaimMainFile;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;
use EasySlugger\Utf8Slugger;

class VendorWarrantyClaimMainFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public static function getClass(): string
    {
        return VendorWarrantyClaimMainFile::class;
    }

    public function getFileProperty(): string
    {
        return 'mainFile';
    }

    public function getDirectory(): string
    {
        return 'purchasing/vendor_warranty_claim';
    }

    /** @param WCVendorWarrantyClaim $subject */
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
