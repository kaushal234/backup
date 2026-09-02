<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim;

use App\Sdk\Downloader;
use App\Sdk\Resource\NonConformityMainFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendor-warranty-claim')]
final class NCRMainFileController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/{id}/main-file/{fileId}', name: 'non-conformity:main-file', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, int $fileId): Response
    {
        return $this->downloader->stream(NonConformityMainFile::class, ['resource_id' => $id, 'file_id' => $fileId]);
    }
}
