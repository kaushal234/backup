<?php

declare(strict_types=1);

namespace AppBundle\Controller\Chat;

use ApiBundle\Http\FileStreamedResponseFactory;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class DownloadFileController
{
    public function __construct(
        private readonly FileStreamedResponseFactory $fileStreamedResponseFactory,
    ) {
    }

    #[Route(path: '/chat/files/{id}/download', name: 'download_ai_file')]
    public function __invoke(int $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('ai_files/%s/download', $id));
    }
}
