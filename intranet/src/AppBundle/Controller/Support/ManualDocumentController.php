<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Form\Type\Support\ManualDocumentType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/support/manual_documents', defaults: ['alvest_module' => 'PUBS'])]
class ManualDocumentController extends AbstractController
{
    /** @var string */
    final public const RESOURCE_URL = 'support/manual_documents';

    /** @var string */
    final public const PARTS_DIAGRAM = 'PARTS DIAGRAM';

    private readonly Client $client;
    private readonly FormFactoryInterface $formFactory;
    private readonly FileManager $fileManager;
    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;
    private readonly TranslatorInterface $translator;
    private readonly ViolationMapper $violationMapper;

    public function __construct(Client $client, FileManager $fileManager, FileStreamedResponseFactory $fileStreamedResponseFactory, FormFactoryInterface $formFactory, TranslatorInterface $translator, ViolationMapper $violationMapper)
    {
        $this->client = $client;
        $this->fileManager = $fileManager;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->violationMapper = $violationMapper;
    }

    #[Route(path: '/{id}/show', name: 'manual_documents_show', methods: ['GET', 'POST'])]
    #[Template('support/manual_document/show.html.twig')]
    public function show(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manualDocument)
    {
        $formFiles = $this->formFactory->createNamed('document_file', SimpleFileType::class);
        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile(
                        $manualDocument,
                        $file,
                        self::RESOURCE_URL,
                        $formFiles->get('description')->getData(),
                        'files',
                        false,
                        true
                    );
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('support.manual.messages.success.file', [], 'support')
                );

                return $this->redirectToRoute('manual_documents_show', ['id' => $manualDocument->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formFiles);
            }
        }

        $parts = [];
        foreach ($manualDocument['parts'] as $part) {
            $part['erp'] = $manualDocument['manual']['equipmentRecord']['manufacturerLocation']['erp'] ?? 0;
            $date = new \DateTime($manualDocument['manual']['createdAt'] ?? 'now');
            $part['date'] = $date->format('Y-m-d');
            $parts[] = $part;
        }

        return [
            'form_files' => $formFiles->createView(),
            'manualDocument' => $manualDocument,
            'parts' => $parts,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'manual_documents_edit', methods: ['GET', 'POST'])]
    #[Template('support/manual_document/edit.html.twig')]
    #[IsGranted('FEATURE_MANUAL_ADMIN')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manualDocument)
    {
        $form = $this->createForm(ManualDocumentType::class, $manualDocument);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->translator->trans('support.manual.messages.success.edit', [], 'support')
                );

                return $this->redirectToRoute('manual_documents_show', ['id' => $manualDocument->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'manualDocument' => $manualDocument,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{manualDocumentId}/files/{id}', name: 'manual_document_file_show', methods: 'GET')]
    public function showFile($manualDocumentId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('support/manual_documents/%s/files/%s', $manualDocumentId, $id));
    }

    #[Route(path: '/{manualDocumentId}/files/{id}/delete', name: 'manual_document_file_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_MANUAL_ADMIN')]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'manualDocumentId'])] ApiData $manualDocument, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('manual_document_file_delete', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('manual_documents_show', ['id' => $manualDocument->getIriId()]);
        }
        $this->fileManager->deleteFile($manualDocument, self::RESOURCE_URL, \sprintf('files/%s', $id));
        $this->addFlash('warning', $this->translator->trans('files.delete_success', [], 'messages'));

        return $this->redirectToRoute('manual_documents_show', ['id' => $manualDocument->getIriId()]);
    }

    #[Route(path: '/{manualDocumentId}/pdf', name: 'manual_document_download_pdf_file', methods: 'GET', requirements: ['manualDocumentId' => '\d+'])]
    public function showManualDocumentPdf(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'manualDocumentId'])] ApiData $manualDocument, int $manualDocumentId)
    {
        if (($manualDocument['document']['extension'] ?? '') === 'pdf') {
            return $this->fileStreamedResponseFactory->create(\sprintf('support/manual_documents/%s/files/%s', $manualDocumentId, Iri::id($manualDocument['document'])));
        }

        return $this->fileStreamedResponseFactory->create(\sprintf(self::RESOURCE_URL.'/%d/pdf/document', $manualDocumentId), ['headers' => ['Accept' => 'application/pdf']]);
    }
}
