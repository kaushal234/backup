<?php

declare(strict_types=1);

namespace AppBundle\Controller\MinutesOfMeeting;

use ApiBundle\Client;
use ApiBundle\ClientExceptionMapper;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\MinutesOfMeeting\MeetingFilterType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\MinutesOfMeeting\MeetingActionType;
use AppBundle\Form\Type\MinutesOfMeeting\MeetingDuplicateType;
use AppBundle\Form\Type\MinutesOfMeeting\MeetingType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Form\Type\SimpleSearchType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/meetings', defaults: ['alvest_module' => 'MOM'])]
class MeetingController extends AbstractController
{
    final public const RESOURCE_URL = 'minutes_of_meeting/meetings';

    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;
    private readonly ClientExceptionMapper $clientExceptionMapper;

    private readonly FileManager $fileManager;

    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, ClientExceptionMapper $clientExceptionMapper, FileManager $fileManager, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->clientExceptionMapper = $clientExceptionMapper;
        $this->fileManager = $fileManager;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
    }

    #[Route(path: '', name: 'meeting_home', methods: ['GET', 'POST'])]
    #[Template('minutes_of_meeting/meetings/home.html.twig')]
    public function home(Request $request)
    {
        $title = $this->translator->trans('meeting.last_10', [], 'meeting');
        $meetings = $this->client->findBy(self::RESOURCE_URL, ['itemsPerPage' => 10], ['createdAt' => 'desc']);

        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $this->client->get(\sprintf('minutes_of_meeting/meetings/%s', $id));

                return $this->redirectToRoute('meeting_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('Meeting #%s does not exist', $id));
            }
        }

        $simpleSearchForm = $this->createForm(SimpleSearchType::class, [], [
            'method' => 'GET',
            'csrf_protection' => false,
            'search_label' => false,
            'search_placeholder' => 'Search for...',
        ]);

        $simpleSearchForm->handleRequest($request);
        if ($simpleSearchForm->isSubmitted() && $simpleSearchForm->isValid()) {
            $title = $this->translator->trans('titles.search_result', [], 'messages');
            $parameters = [
                'q' => $simpleSearchForm->get('q')->getData(),
                'itemsPerPage' => 100,
            ];
            $meetings = $this->client->findBy(self::RESOURCE_URL, $parameters, ['createdAt' => 'desc']);
        }

        return [
            'meetings' => $meetings,
            'idSearchForm' => $idSearchForm->createView(),
            'simpleSearchForm' => $simpleSearchForm->createView(),
            'title' => $title,
        ];
    }

    #[Route(path: '/search', name: 'meeting_search', methods: ['GET', 'POST'])]
    #[Template('minutes_of_meeting/meetings/search.html.twig')]
    public function search(Request $request)
    {
        $formFilter = $this
            ->formFactory
            ->createNamed(
                '',
                MeetingFilterType::class, [],
                [
                    'action' => $this->generateUrl('meeting_search'),
                    'method' => 'GET',
                ])
        ;

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = [];
            $parameters['page'] = $request->query->getInt('page', 1);
            $parameters['itemsPerPage'] = 50;
            $parameters = array_merge($parameters, $formFilter->getData());

            $meetings = $this->client->findBy(self::RESOURCE_URL, $parameters, ['createdAt' => 'desc']);

            return [
                'currentPage' => $parameters['page'],
                'paginationUrl' => [
                    'route' => $request->attributes->get('_route'),
                    'parameters' => $request->query->all(),
                ],
                'meetings' => $meetings,
                'formFilter' => $formFilter->createView(),
            ];
        }

        return [
            'formFilter' => $formFilter->createView(),
        ];
    }

    #[Route(path: '/my-mom', name: 'meeting_search_my_mom', methods: ['GET', 'POST'])]
    #[Template('minutes_of_meeting/meetings/search.html.twig')]
    public function myMinutesOfMeeting(Request $request)
    {
        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['itemsPerPage'] = 50;
        $meetings = $this->client->findBy(self::RESOURCE_URL, ['mine' => true] + $parameters, ['createdAt' => 'desc']);

        return [
            'currentPage' => $parameters['page'],
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
            'meetings' => $meetings,
            'title' => 'meeting.title.my_mom_title',
        ];
    }

    #[Route(path: '/{id}/show', name: 'meeting_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Template('minutes_of_meeting/meetings/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $meeting, Request $request)
    {
        $formFiles = $this->formFactory->createNamed('meeting_file', SimpleFileType::class);

        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile($meeting, $file, 'minutes_of_meeting/meetings', $formFiles->get('description')->getData(), 'files', true);
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('customers.messages.success.file', [], 'sales_customers')
                );

                return $this->redirectToRoute('meeting_show', ['id' => $meeting->getIriId(), 'tab' => 'files']);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formFiles);
            }
        }

        return [
            'formFiles' => $formFiles->createView(),
            'meeting' => $meeting,
            'tab' => $request->query->get('tab'),
        ];
    }

    #[Route(path: '/{id}/new-task', name: 'meeting_new_task', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Template('minutes_of_meeting/meetings/task.html.twig')]
    #[IsGranted(attribute: 'MEETING_WRITE_VOTER', subject: new Expression('args["meeting"].getIri()'))]
    public function addTask(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $meeting, Request $request)
    {
        $formAction = $this
            ->formFactory
            ->createNamed(
                'meeting_action',
                MeetingActionType::class, [],
                []);

        $formAction->handleRequest($request);
        if ($formAction->isSubmitted() && $formAction->isValid()) {
            try {
                $data = $formAction->getData();
                $data['resource'] = $meeting->getIri();

                $this->client->save('minutes_of_meeting/actions', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('meeting.add.action', [], 'meeting')
                );

                return $this->redirectToRoute('meeting_show', ['id' => $meeting->getIriId(), 'tab' => 'tasks']);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formAction);
            }
        }

        return [
            'formAction' => $formAction->createView(),
            'meeting' => $meeting,
            'can_write_meeting' => true,
        ];
    }

    #[Route(path: '/add', name: 'meeting_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'meeting_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[Template('minutes_of_meeting/meetings/form.html.twig')]
    public function form(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $meeting = null)
    {
        $canWriteMeeting = null === $meeting || $this->isGranted('MEETING_WRITE_VOTER', $meeting['@id']);
        if (!$canWriteMeeting) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->formFactory->createNamed('meeting_type', MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $message = null === $meeting ? $this->translator->trans('meeting.add.success', [], 'meeting') : $this->translator->trans('meeting.edit.success', [], 'meeting');

                $savedMeeting = $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash('success', $message);

                return $this->redirectToRoute('meeting_show', [
                    'id' => $savedMeeting['id'],
                ]);
            } catch (ClientException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
            'meeting' => $meeting,
            'can_write_meeting' => $canWriteMeeting,
        ];
    }

    #[Route(path: '/{id}/status/{status}', name: 'meeting_status', requirements: ['id' => '\d+', 'status' => 'RELEASED|CLOSED'], methods: 'GET')]
    #[IsGranted(attribute: 'MEETING_WRITE_VOTER', subject: new Expression('args["meeting"].getIri()'))]
    public function status(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $meeting, $status): RedirectResponse
    {
        try {
            $this->client->put(\sprintf(self::RESOURCE_URL.'/%d/status', $meeting->getIriId()), ['json' => ['status' => $status]]);
        } catch (ClientException $e) {
            $this->addFlash('error', nl2br((string) $this->clientExceptionMapper->mapToString($e)));
        }

        return $this->redirectToRoute('meeting_show', ['id' => $meeting->getIriId()]);
    }

    #[Route(path: '/{id}/delete', name: 'meeting_delete', requirements: ['id' => '\d+'], methods: ['GET', 'DELETE'])]
    #[IsGranted(attribute: 'MEETING_WRITE_VOTER', subject: new Expression('args["meeting"].getIri()'))]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $meeting, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_mom', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('meeting.delete.error', [], 'meeting'));

            return $this->redirectToRoute('meeting_show', ['id' => $meeting->getIriId()]);
        }

        try {
            $this->client->remove(self::RESOURCE_URL, $meeting['id']);

            $this->addFlash(
                'success',
                $this->translator->trans('meeting.delete.success', [], 'meeting')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('meeting.delete.error', [], 'meeting')
            );

            return $this->redirectToRoute('meeting_show', ['id' => $meeting->getIriId()]);
        }

        return $this->redirectToRoute('meeting_home');
    }

    #[Route(path: '/{meetingId}/files/{id}', name: 'meeting_download_file', methods: 'GET')]
    public function showFile($meetingId, $id): StreamedResponse
    {
        return $this->fileStreamedResponseFactory->create(\sprintf(self::RESOURCE_URL.'/%s/files/%s', $meetingId, $id));
    }

    #[Route(path: '/{meetingId}/files/{id}/delete', name: 'meeting_delete_file', methods: ['GET'], requirements: ['meetingId' => '\d+'])]
    public function deleteFile(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'meetingId'])] ApiData $meeting,
        $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('meeting_delete_file', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete file: please refresh your form.');

            return $this->redirectToRoute('meeting_show', ['id' => $meeting->getIriId()]);
        }

        try {
            $this->fileManager->deleteFile($meeting, self::RESOURCE_URL, \sprintf('files/%s', $id));
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('first_article_qualification.messages.error.delete_file', [], 'first_article_qualification')
            );
        }

        return $this->redirectToRoute('meeting_show', ['id' => $meeting->getIriId(), 'tab' => 'files']);
    }

    #[Route(path: '/{meetingId}/summary', name: 'meeting_download_summary_pdf_file', methods: 'GET', requirements: ['meetingId' => '\d+'])]
    public function showSummaryPDF($meetingId): StreamedResponse
    {
        return $this->fileStreamedResponseFactory->create(\sprintf(self::RESOURCE_URL.'/%s/summary', $meetingId), ['headers' => ['Accept' => 'application/pdf']]);
    }

    #[Route(path: '/{id}/duplicate', name: 'meeting_duplicate', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Template('minutes_of_meeting/meetings/duplicate.html.twig')]
    #[IsGranted(attribute: 'MEETING_WRITE_VOTER', subject: new Expression('args["meeting"].getIri()'))]
    public function duplicateMeeting(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $meeting, Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'meeting_duplicate_type_form',
                MeetingDuplicateType::class,
                [
                    'meetingDate' => (new \DateTime())->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                    'location' => $meeting['location'],
                    'description' => $meeting['description'],
                ]
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $newMeeting = $this->client->post(\sprintf('%s/duplicate', $meeting->getIri()), ['json' => $data]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('meeting.duplicate.success', [], 'meeting')
                );

                return $this->redirectToRoute('meeting_show', ['id' => Iri::id($newMeeting)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'actionPath' => $this->generateUrl('meeting_duplicate', ['id' => $meeting->getIriId()]),
            'form' => $form->createView(),
            'meeting' => $meeting,
        ];
    }
}
