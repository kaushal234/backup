<?php

declare(strict_types=1);

namespace App\Controller\Manufacturing;

use App\Factory\FileResponseFactory;
use App\FileSystem\Image\VaultPartImageFileProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InventoryImageController extends AbstractController
{
    public function __construct(
        private readonly FileResponseFactory $fileResponseFactory,
        private readonly VaultPartImageFileProvider $vaultPartImageFileProvider,
    ) {
    }

    #[Route(path: '/inventories/{item}/images/{index}', name: 'manufacturing_inventory_image', methods: ['GET'])]
    public function __invoke(string $item, int $index, Request $request): Response
    {
        $files = $this->vaultPartImageFileProvider->getAll($item);

        if (!isset($files[$index - 1])) {
            throw $this->createNotFoundException('Image could not be found.');
        }

        $file = $files[$index - 1];
        if (!$file instanceof File) {
            throw $this->createNotFoundException('Image could not be found.');
        }

        $response = $this->fileResponseFactory->createFileResponse($file);
        $response->setContentDisposition('inline', $file->getFilename());

        return $response;
    }
}
