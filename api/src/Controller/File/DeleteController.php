<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\FileSystem\Persistence\PersistableFileManagerFactory;
use App\Manager\EntityFileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

class DeleteController extends AbstractController
{
    private readonly EntityFileManager $entityFileManager;
    private readonly PersistableFileManagerFactory $persistableFileManagerRegistry;

    private readonly ParameterBagInterface $parameters;

    public function __construct(EntityFileManager $entityFileManager, PersistableFileManagerFactory $persistableFileManagerRegistry, ParameterBagInterface $parameters)
    {
        $this->entityFileManager = $entityFileManager;
        $this->persistableFileManagerRegistry = $persistableFileManagerRegistry;
        $this->parameters = $parameters;
    }

    public function __invoke($data, string $parentProperty, string $class, int $fileId): Response
    {
        $file = $this->entityFileManager->getFileFromEntity($class, $fileId, $data, $parentProperty);

        try {
            $fileToDelete = new File($this->parameters->get('legacy.upload_dir').'/'.$file->getFilePath());
        } catch (FileNotFoundException $e) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->persistableFileManagerRegistry->getManagerForClass($class)->detach(
            $data,
            $fileToDelete
        );

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
