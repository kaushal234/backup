<?php

declare(strict_types=1);

namespace App\Controller\ServiceBulletin;

use App\Sdk\Downloader;
use App\Sdk\Resource\ServiceBulletinFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/service-bulletin')]
final class FileStreamController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/{id}/files-stream', name: 'service_bulletin:file_stream', methods: [Request::METHOD_GET])]
    public function __invoke(int $id): Response
    {
        return $this->downloader->stream(ServiceBulletinFile::class, ['id' => $id]);
    }
}
