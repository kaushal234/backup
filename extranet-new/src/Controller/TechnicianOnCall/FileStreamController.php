<?php

declare(strict_types=1);

namespace App\Controller\TechnicianOnCall;

use App\Sdk\Downloader;
use App\Sdk\Resource\TechnicianOnCall;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/technician-on-calls')]
final class FileStreamController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/{id}/files-stream/{fileId}', name: 'technician_on_call:file_stream', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->stream(TechnicianOnCall::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
