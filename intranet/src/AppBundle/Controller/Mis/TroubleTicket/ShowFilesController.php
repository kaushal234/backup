<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class ShowFilesController extends AbstractController
{
    public function __construct(
        private readonly FileManager $fileManager,
        private readonly TranslatorInterface $translator,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route(path: '/{id}/show/files', name: 'trouble_ticket_show_files', methods: ['GET|POST'])]
    #[Template('mis/trouble_ticket/show_files.html.twig')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket, Request $request)
    {
        $formFiles = $this->container->get('form.factory')->createNamed('form_files', SimpleFileType::class);
        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile(
                        $troubleTicket,
                        $file,
                        ShowController::RESOURCE_URL,
                        $formFiles->get('description')->getData(),
                        'files',
                        true
                    );
                }

                $this->addFlash('success', $this->translator->trans('files.upload_success', [], 'messages'));

                return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId(), 'tab' => 'files']);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formFiles);
            }
        }

        return [
            'troubleTicket' => $troubleTicket,
            'form' => $formFiles->createView(),
        ];
    }
}
