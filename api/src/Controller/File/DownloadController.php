<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\Entity\ConfidentialInterface;
use App\Factory\FileResponseFactory;
use App\Manager\EntityFileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class DownloadController extends AbstractController
{
    private readonly EntityFileManager $entityFileManager;
    private readonly FileResponseFactory $fileResponseFactory;

    private readonly ParameterBagInterface $parameters;

    public function __construct(EntityFileManager $entityFileManager, FileResponseFactory $fileResponseFactory, ParameterBagInterface $parameters)
    {
        $this->entityFileManager = $entityFileManager;
        $this->fileResponseFactory = $fileResponseFactory;
        $this->parameters = $parameters;
    }

    public function __invoke($data, string $class, string $parentProperty, int $fileId): BinaryFileResponse
    {
        $file = $this->entityFileManager->getFileFromEntity($class, $fileId, $data, $parentProperty);

        try {
            $responseFile = new File($this->parameters->get('legacy.upload_dir').'/'.$file->getFilePath());
            $response = $this->fileResponseFactory->createFileResponse($responseFile);

            $reflectionClass = new \ReflectionClass($data);
            $filename = $reflectionClass->implementsInterface(ConfidentialInterface::class) && $data->isConfidential() ? \sprintf('confidential-%s', $responseFile->getFilename()) : $responseFile->getFilename();
            $response->setContentDisposition('inline', $filename);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Attached file could not be found.');
        }

        return $response;
    }
}
