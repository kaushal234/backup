<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Http\FileStreamedResponseFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
readonly class DownloadFileController
{
    public function __construct(
        private FileStreamedResponseFactory $fileStreamedResponseFactory,
    ) {
    }

    #[Route(path: '/{troubleTicketId}/files/{id}', name: 'trouble_ticket_files_show', requirements: ['id' => '\d+'], methods: 'GET')]
    public function __invoke(int $troubleTicketId, int $id): StreamedResponse
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('%s/%s/files/%s', ShowController::RESOURCE_URL, $troubleTicketId, $id));
    }
}
