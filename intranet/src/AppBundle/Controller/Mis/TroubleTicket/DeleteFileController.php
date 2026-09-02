<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Manager\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class DeleteFileController extends AbstractController
{
    public function __construct(
        private readonly FileManager $fileManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/{troubleTicketId}/files/{id}/delete', name: 'trouble_ticket_file_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_TROUBLE_TICKET_DELETE_FILE')]
    public function __invoke(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL, 'id' => 'troubleTicketId'])] ApiData $troubleTicket, int $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_trouble_ticket_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
        }
        $this->fileManager->deleteFile($troubleTicket, ShowController::RESOURCE_URL, \sprintf('files/%s', $id));
        $this->addFlash('success', $this->translator->trans('delete.success', [], 'file_type'));

        return $this->redirectToRoute('trouble_ticket_show_files', ['id' => $troubleTicket->getIriId()]);
    }
}
