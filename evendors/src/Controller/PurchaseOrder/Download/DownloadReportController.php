<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder\Download;

use App\Sdk\Downloader;
use App\Sdk\Resource\PurchaseOrder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/purchase-order/download/report')]
final class DownloadReportController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/{after}/{before}', name: 'purchase-order:download:report', methods: [Request::METHOD_GET])]
    public function __invoke(string $after, string $before): Response
    {
        return $this->downloader->downloadExcel(PurchaseOrder::class, ['orderDate' => ['after' => $after, 'before' => $before]], 'purchase_orders_'.mb_substr($after, 0, -9).'_'.mb_substr($before, 0, -9));
    }
}
