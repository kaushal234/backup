<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim;

use App\Sdk\Downloader;
use App\Sdk\Resource\VendorWarrantyClaimMainFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendor-warranty-claim')]
final class VendorWarrantyClaimMainFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/{id}/vwc-main-file/{fileId}', name: 'vendor-warranty-claim:main-file', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->stream(VendorWarrantyClaimMainFile::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
