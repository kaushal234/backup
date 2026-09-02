<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim;

use App\Sdk\Downloader;
use App\Sdk\Resource\NonConformity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DownloadNCRFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route(path: '/quality/non_conformities/{id}/file/{fileId}', name: 'non-conformity:file-download', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->download(NonConformity::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
