<?php

declare(strict_types=1);

namespace App\Controller\Activity;

use App\Sdk\Downloader;
use App\Sdk\Resource\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(path: '/comments')]
final class FileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/{id}/files/{fileId}', name: 'comments:file', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->download(Comment::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
