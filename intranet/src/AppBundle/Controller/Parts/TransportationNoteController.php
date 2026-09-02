<?php

declare(strict_types=1);

namespace AppBundle\Controller\Parts;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Parts\EditTransportationNoteType;
use AppBundle\Form\Type\Parts\TransportationNoteType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/parts/transportation-note')]
class TransportationNoteController extends AbstractController
{
    private readonly Client $client;
    private readonly ViolationMapper $violationMapper;
    private readonly TranslatorInterface $translator;
    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    public function __construct(Client $client, ViolationMapper $violationMapper, TranslatorInterface $translator, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        $this->client = $client;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
    }

    #[Route(path: '', name: 'parts_transportation_note_home', methods: 'GET|POST')]
    #[Template('parts/transportation_note/home.html.twig')]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/transportation_notes', 'filters' => ['order' => ['updatedAt' => 'DESC']]])] HydraCollection $transportationNotes)
    {
        return ['notes' => $transportationNotes];
    }

    #[Route(path: '/{id}/show', name: 'parts_transportation_note_show', methods: 'GET')]
    #[Template('parts/transportation_note/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/transportation_notes'])] ApiData $transportationNote)
    {
        return ['note' => $transportationNote];
    }

    #[Route(path: '/add', name: 'parts_transportation_note_add', methods: 'GET|POST')]
    #[Template('parts/transportation_note/add_edit.html.twig')]
    #[IsGranted('FEATURE_TRANSPORTATION_NOTE_WRITE')]
    public function add(Request $request)
    {
        $form = $this->createForm(TransportationNoteType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $note = $this->client->save('parts/transportation_notes', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('transportation_notes.messages.success.create', [], 'transportation_notes')
                );

                return $this->redirectToRoute('parts_transportation_note_show', ['id' => $note['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return ['form' => $form->createView()];
    }

    #[Route(path: '/{id}/edit', name: 'parts_transportation_note_edit', methods: 'GET|POST')]
    #[Template('parts/transportation_note/add_edit.html.twig')]
    #[IsGranted('FEATURE_TRANSPORTATION_NOTE_WRITE')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/transportation_notes'])] ApiData $transportationNote, Request $request)
    {
        $form = $this->createForm(EditTransportationNoteType::class, $transportationNote);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var ApiData $data */
                $data = $form->getData();
                if ($data->offsetExists('note') && null === $data->offsetGet('note')) {
                    $data->offsetSet('note', '');
                }
                $this->client->save('parts/transportation_notes', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('transportation_notes.messages.success.edit', [], 'transportation_notes')
                );

                return $this->redirectToRoute('parts_transportation_note_show', ['id' => $transportationNote['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'note' => $transportationNote,
        ];
    }

    #[Route(path: '/{id}/logs', name: 'parts_transportation_note_logs', methods: 'GET')]
    #[Template('parts/transportation_note/logs.html.twig')]
    public function logs(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/transportation_notes'])] ApiData $transportationNote)
    {
        return ['note' => $transportationNote];
    }

    /**
     * @return StreamedResponse
     */
    #[Route(path: '/{noteId}/files/{id}', name: 'parts_transportation_note_file_show', requirements: ['id' => '\d+', 'noteId' => '\d+'], methods: 'GET')]
    public function showFile($noteId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('parts/transportation_notes/%s/files/%s', $noteId, $id));
    }

    #[Route(path: '/{noteId}/files/{id}/delete', name: 'parts_transportation_note_file_delete', requirements: ['id' => '\d+', 'noteId' => '\d+'], methods: 'GET')]
    #[IsGranted('FEATURE_TRANSPORTATION_NOTE_WRITE')]
    public function deleteFile(Request $request, $noteId, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_transportation_notes_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('parts_transportation_note_show', ['id' => $noteId]);
        }

        try {
            $this->client->request('parts/transportation_notes', $noteId, \sprintf('files/%s', $id), Request::METHOD_DELETE);
            $this->addFlash('success', $this->translator->trans('transportation_notes.messages.success.delete_file', [], 'transportation_notes'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('transportation_notes.messages.errors.delete_file', [], 'transportation_notes'));
        }

        return $this->redirectToRoute('parts_transportation_note_show', ['id' => $noteId]);
    }

    #[Route(path: '/{id}/show_ajax', name: 'parts_transportation_note_file_ajax', requirements: ['id' => '\d+'], methods: 'GET', condition: 'request.isXmlHttpRequest()')]
    #[Template('parts/transportation_note/files_ajax.html.twig')]
    #[IsGranted('FEATURE_TRANSPORTATION_NOTE_WRITE')]
    public function noteFilesAjax(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/transportation_notes'])] ApiData $transportationNote)
    {
        return ['note' => $transportationNote];
    }
}
