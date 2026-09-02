<?php

declare(strict_types=1);

namespace App\Controller;

use App\Sdk\Downloader;
use App\Sdk\Resource\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DownloadCommentFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/comments/{id}/file/{fileId}', name: 'comment:file-download', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->download(Comment::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
