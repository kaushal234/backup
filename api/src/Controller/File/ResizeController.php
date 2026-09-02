<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\Dto\FileInput;
use App\Entity\File;
use App\FileSystem\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResizeController extends AbstractController
{
    public function __construct(
        private readonly ImageManager $imageManager,
        private readonly string $legacyUploadDir,
    ) {
    }

    public function __invoke(FileInput $data, Request $request): Response
    {
        $previousFile = $request->attributes->get('previous_data');
        /** @var File $previousFile */
        $file = new \SplFileObject($this->legacyUploadDir.'/'.$previousFile->getFilePath());
        $fileContent = $this->imageManager->resize($file, $data->width, $data->height);
        $file->openFile('w')->fwrite($fileContent);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
