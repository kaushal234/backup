<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Manager\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ImageManager $imageManager,
    ) {
    }

    #[Route(path: '/uploads/{filePath}', name: 'upload_access', requirements: ['filePath' => '.+'], methods: 'GET')]
    public function access(Request $request, string $filePath)
    {
        $widthParam = $request->query->get('width');
        $width = null !== $widthParam ? (int) $widthParam : null;

        $folderPath = realpath($this->getParameter('upload_dir'));

        $path = $folderPath.'/'.$filePath;
        if (!$this->filesystem->isAbsolutePath($path) || !$this->filesystem->exists($path)) {
            throw $this->createNotFoundException('File not found.');
        }

        if (!empty($width)) {
            // For legacy compatibility
            $path = $this->imageManager->resize($path, $width);
        }

        $response = new BinaryFileResponse($path);
        BinaryFileResponse::trustXSendfileTypeHeader();

        return $response;
    }
}
