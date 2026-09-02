<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\Sdk\Downloader;
use App\Sdk\Resource\ManualDocumentPublic;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route('/public/manual_documents')]
final class ManualDocumentPublicFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/{manualDocumentId}/file/{fileId}', name: 'public_manual_document_file', methods: [Request::METHOD_GET])]
    public function __invoke(int $manualDocumentId, int $fileId): Response
    {
        return $this->downloader->stream(ManualDocumentPublic::class, ['resource_id' => $manualDocumentId, 'file_id' => $fileId]);
    }
}
