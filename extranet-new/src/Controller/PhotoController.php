<?php

declare(strict_types=1);

namespace App\Controller;

use App\Sdk\Downloader;
use App\Sdk\Resource\Photo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route('/people')]
final class PhotoController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/{id}/photo/{fileId}', name: 'people-photo', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->stream(Photo::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
