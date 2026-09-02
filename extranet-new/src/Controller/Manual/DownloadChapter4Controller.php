<?php

declare(strict_types=1);

namespace App\Controller\Manual;

use App\Sdk\Downloader;
use App\Sdk\Resource\Manual;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/manuals')]
class DownloadChapter4Controller
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/{id}/chapter-4', name: 'manual:download_chapter4', methods: [Request::METHOD_GET])]
    public function __invoke(int $id): Response
    {
        return $this->downloader->download(Manual::class, ['resource_id' => $id]);
    }
}
