<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use LegacyBundle\Entity\EquipmentRecordFile;
use LegacyBundle\Factory\LegacyFileFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class DownloadEquipmentRecordFileController extends AbstractController
{
    public function __construct(
        private readonly LegacyFileFactory $legacyFileFactory
    ) {
    }

    public function __invoke(EquipmentRecordFile $data): BinaryFileResponse
    {
        try {
            // Customer files physically live under "{legacy.upload_dir}/service_files/{filename}".
            $file = $this->legacyFileFactory->createLegacyFileFromPath(\sprintf('service_files/%s', $data->filename));

            return $this->file($file, $data->filename);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Attached file could not be found.');
        }
    }
}
