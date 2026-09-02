<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder\Download;

use App\Sdk\Downloader;
use App\Sdk\Resource\PurchaseOrder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/purchase-order/download/all')]
final class DownloadAllController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/xlsx', name: 'purchase-order:download:all', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        return $this->downloader->downloadExcel(PurchaseOrder::class, [], 'purchase-orders');
    }
}
