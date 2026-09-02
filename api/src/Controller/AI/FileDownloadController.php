<?php

declare(strict_types=1);

namespace App\Controller\AI;

use App\Entity\AI\AIFile;
use App\Factory\FileResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class FileDownloadController extends AbstractController
{
    public function __construct(
        private readonly FileResponseFactory $fileResponseFactory,
        private readonly string $legacyUploadDir,
    ) {
    }

    public function __invoke(AIFile $data): BinaryFileResponse
    {
        try {
            $responseFile = new File($this->legacyUploadDir.'/'.$data->getFilePath());
            $response = $this->fileResponseFactory->createFileResponse($responseFile);

            $response->setContentDisposition('inline', $responseFile->getFilename());
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Attached file could not be found.');
        }

        return $response;
    }
}
