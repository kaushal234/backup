<?php

declare(strict_types=1);

namespace App\Controller\SupplierCorrectiveActionRequest;

use App\Sdk\Downloader;
use App\Sdk\Resource\SupplierCorrectiveActionRequestMainFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/supplier-corrective-action-request')]
final class MainFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/{id}/main-file/{fileId}', name: 'supplier-corrective-action-request:main-file', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->stream(SupplierCorrectiveActionRequestMainFile::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
