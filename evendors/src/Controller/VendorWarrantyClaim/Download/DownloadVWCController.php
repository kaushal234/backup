<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim\Download;

use App\Sdk\Downloader;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendor-warranty-claim/download/all')]
final class DownloadVWCController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/xlsx', name: 'vendor-warranty-claim:download:all', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        return $this->downloader->downloadExcel(VendorWarrantyClaimInterface::class, [], 'vendor-warranty-claims');
    }
}
