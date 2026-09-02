<?php

declare(strict_types=1);

namespace App\Controller\ManualDocument;

use App\Sdk\Downloader;
use App\Sdk\Resource\ManualDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/manual-documents')]
class DownloadController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/{id}/download/{fileId}/{extension}', name: 'manual_document:download', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId, Request $request, ?string $extension = null): Response
    {
        return $this->downloader->download(ManualDocument::class, ['resource_id' => $id, 'file_id' => $fileId, 'extension' => $extension]);
    }
}
