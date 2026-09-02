<?php

declare(strict_types=1);

namespace App\Controller\SupplierCorrectiveActionRequest;

use App\Sdk\Downloader;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DownloadFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/quality/supplier_corrective_action_requests/{id}/file/{fileId}', name: 'supplier-corrective-action-request:file-download', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->download(SupplierCorrectiveActionRequest::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
