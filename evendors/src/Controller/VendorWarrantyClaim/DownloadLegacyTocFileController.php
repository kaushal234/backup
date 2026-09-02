<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim;

use App\Sdk\Downloader;
use App\Sdk\Resource\WCVendorWarrantyClaim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DownloadLegacyTocFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/purchasing/wc_vendor_warranty_claims/{id}/legacy_toc_file/{fileId}', name: 'vendor-warranty-claim:legacy-toc-file-download-wc', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->download(WCVendorWarrantyClaim::class, ['resource_id' => $id, 'file_id' => $fileId, 'type' => 'toc']);
    }
}
