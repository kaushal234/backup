<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\Factory\FileResponseFactory;
use App\Factory\VaultFileDownloadableInterface;
use App\FileSystem\VaultPartFileProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DrawingController extends AbstractController
{
    public function __construct(
        private readonly VaultPartFileProvider $vaultFileProvider,
        private readonly FileResponseFactory $fileResponseFactory)
    {
    }

    public function __invoke(VaultFileDownloadableInterface $data): BinaryFileResponse
    {
        return $this->vaultFileProvider->getPartFileStreamResponse($data, $this->fileResponseFactory);
    }
}
