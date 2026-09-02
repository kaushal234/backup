<?php

declare(strict_types=1);

namespace App\Controller\Document;

use App\Sdk\Downloader;
use App\Sdk\Resource\Document;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/document')]
final class DownloadController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/download/{id}', name: 'document:download', methods: [Request::METHOD_GET])]
    public function __invoke(
        string $id,
        #[MapQueryParameter] ?string $filename = null,
    ): BinaryFileResponse {
        return $this->downloader->download(Document::class, $id, $filename);
    }
}
