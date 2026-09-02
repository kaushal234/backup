<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use LegacyBundle\Entity\ModFile;
use LegacyBundle\Factory\LegacyFileFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class DownloadFileController extends AbstractController
{
    public function __construct(
        private readonly LegacyFileFactory $legacyFileFactory
    ) {
    }

    public function __invoke(ModFile $data): BinaryFileResponse
    {
        try {
            $file = $this->legacyFileFactory->createLegacyFileFromPath($data->file->getFilePath());

            return $this->file(
                $file,
                $data->file->filename,
            );
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Attached file could not be found.');
        }
    }
}
