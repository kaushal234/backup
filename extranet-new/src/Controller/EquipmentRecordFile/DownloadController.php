<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecordFile;

use App\Sdk\Downloader;
use App\Sdk\Resource\EquipmentRecordFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/equipment-record-file')]
final class DownloadController
{
    public function __construct(
        private readonly Downloader $downloader,
    ) {
    }

    #[Route('/download/{id}', name: 'equipment_record_file:download', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function __invoke(
        int $id,
        #[MapQueryParameter] ?string $filename = null,
    ): BinaryFileResponse {
        return $this->downloader->download(EquipmentRecordFile::class, ['id' => $id], $filename);
    }
}
