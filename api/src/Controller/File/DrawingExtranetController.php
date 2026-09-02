<?php

declare(strict_types=1);

namespace App\Controller\File;

use App\Factory\FileResponseFactory;
use App\Factory\VaultFileDownloadableInterface;
use App\FileSystem\VaultPartFileProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DrawingExtranetController extends AbstractController
{
    private readonly VaultPartFileProvider $vaultFileProvider;
    private readonly FileResponseFactory $fileResponseFactory;

    public function __construct(VaultPartFileProvider $vaultFileProvider, FileResponseFactory $fileResponseFactory)
    {
        $this->vaultFileProvider = $vaultFileProvider;
        $this->fileResponseFactory = $fileResponseFactory;
    }

    public function __invoke(Request $request, VaultFileDownloadableInterface $data, string $project, string $signalCode): BinaryFileResponse
    {
        if ($this->isGranted('BILL_OF_MATERIAL_EXTRANET_VOTER', [
            'drawing' => $data,
            'project' => $project,
            'signalCode' => $signalCode,
        ])) {
            return $this->vaultFileProvider->getPartFileStreamResponse($data, $this->fileResponseFactory);
        }
        throw new AccessDeniedException('Access Denied.');
    }
}
