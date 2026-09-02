<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim;

use App\Sdk\Downloader;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DownloadFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/purchasing/ncr_vendor_warranty_claims/{id}/file/{fileId}', name: 'vendor-warranty-claim:file-download-ncr', methods: [Request::METHOD_GET])]
    #[Route(path: '/purchasing/wc_vendor_warranty_claims/{id}/file/{fileId}', name: 'vendor-warranty-claim:file-download-wc', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->download(VendorWarrantyClaimInterface::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
