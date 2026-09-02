<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Jira\IssueType;
use AppBundle\Form\Type\Mis\TroubleTicket\MISAssigneeChoiceType;
use AppBundle\Form\Type\Mis\TroubleTicket\TroubleTicketCommentTransferType;
use AppBundle\Manager\Task\TaskManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class ShowController extends AbstractController
{
    /** @var string */
    public const JIRA_ISSUE_URL = 'jira/trouble_ticket_issues';

    /** @var string */
    public const RESOURCE_URL = 'mis/trouble_tickets';

    /** @var string */
    public const RESOURCE_URL_TYPE = 'mis/types';

    public function __construct(
        private readonly Client $client,
        private readonly TaskManager $taskManager,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route(path: '/{id}/show', name: 'trouble_ticket_show', methods: ['GET|POST'])]
    #[Route(path: '/{id}/request-information', name: 'trouble_ticket_request_information', defaults: ['label' => 'trouble_ticket.button.request_information'], methods: ['GET|POST'])]
    #[Route(path: '/{id}/comment', name: 'trouble_ticket_comment', defaults: ['label' => 'trouble_ticket.button.comment'], methods: ['GET|POST'])]
    #[Route(path: '/{id}/send-to-mis', name: 'trouble_ticket_send_to_mis', defaults: ['label' => 'trouble_ticket.button.send_to_mis'], methods: ['GET|POST'])]
    #[Route(path: '/{id}/send-to-moo', name: 'trouble_ticket_send_to_moo', defaults: ['label' => 'trouble_ticket.button.send_to_moo'], methods: ['GET|POST'])]
    #[Route(path: '/{id}/propose-solution', name: 'trouble_ticket_propose_solution', defaults: ['label' => 'trouble_ticket.button.propose_solution'], methods: ['GET|POST'])]
    #[Route(path: '/{id}/reopen', name: 'trouble_ticket_reopen', defaults: ['label' => 'trouble_ticket.button.reopen'], methods: 'GET|POST')]
    #[Route(path: '/{id}/transfer-to-jira', name: 'trouble_ticket_transfer_jira', defaults: ['label' => 'trouble_ticket.button.send_to_jira'], methods: ['GET|POST'])]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $troubleTicket, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $url = '';
        switch ($route = $request->attributes->get('_route')) {
            case 'trouble_ticket_request_information':
                $form = $this->createForm(TroubleTicketCommentTransferType::class, $troubleTicket, [
                    'request_information' => true,
                    'action' => $this->generateUrl('trouble_ticket_request_information', ['id' => $troubleTicket->getIriId()]),
                ]);
                $title = 'request_information';
                $url = \sprintf('%s/%s/transfer', self::RESOURCE_URL, $troubleTicket->getIriId());
                break;
            case 'trouble_ticket_comment':
                $form = $this->createForm(TroubleTicketCommentTransferType::class, $troubleTicket, [
                    'comment' => true,
                    'action' => $this->generateUrl('trouble_ticket_comment', ['id' => $troubleTicket->getIriId()]),
                ]);
                $title = 'comment';
                $url = \sprintf('%s/%s/comment', self::RESOURCE_URL, $troubleTicket->getIriId());
                break;
            case 'trouble_ticket_reopen':
                $form = $this->createForm(TroubleTicketCommentTransferType::class, $troubleTicket, [
                    'action' => $this->generateUrl('trouble_ticket_reopen', ['id' => $troubleTicket->getIriId()]),
                ]);
                $title = 'reopen';
                $url = \sprintf('%s/%s/reopen', self::RESOURCE_URL, $troubleTicket->getIriId());
                break;
            case 'trouble_ticket_send_to_mis':
                $form = $this->createForm(TroubleTicketCommentTransferType::class, $troubleTicket, [
                    'send_to_mis' => true,
                    'action' => $this->generateUrl('trouble_ticket_send_to_mis', ['id' => $troubleTicket->getIriId()]),
                ]);
                $title = 'send_to_mis';
                $url = \sprintf('%s/%s/transfer', self::RESOURCE_URL, $troubleTicket->getIriId());
                break;
            case 'trouble_ticket_send_to_moo':
                $form = $this->createForm(TroubleTicketCommentTransferType::class, $troubleTicket, [
                    'send_to_moo' => true,
                    'action' => $this->generateUrl('trouble_ticket_send_to_moo', ['id' => $troubleTicket->getIriId()]),
                ]);
                $title = 'send_to_moo';
                $url = \sprintf('%s/%s/transfer', self::RESOURCE_URL, $troubleTicket->getIriId());
                break;
            case 'trouble_ticket_propose_solution':
                $form = $this->createForm(TroubleTicketCommentTransferType::class, $troubleTicket, [
                    'propose_solution' => true,
                    'action' => $this->generateUrl('trouble_ticket_propose_solution', ['id' => $troubleTicket->getIriId()]),
                ]);
                $title = 'propose_solution';
                $url = \sprintf('%s/%s/transfer', self::RESOURCE_URL, $troubleTicket->getIriId());
                break;
            case 'trouble_ticket_transfer_jira':
                $form = $this->createForm(IssueType::class, [], [
                    'projectId' => $troubleTicket['module']['application']['jiraProjectId'],
                    'action' => $this->generateUrl('trouble_ticket_transfer_jira', ['id' => $troubleTicket->getIriId()]),
                ]);
                $title = 'transfer to jira';
                break;
            default:
                $form = null;
                $url = \sprintf('%s/%s/comment', self::RESOURCE_URL, $troubleTicket->getIriId());
                $title = null;
        }

        if (null !== $form && 'transfer to jira' !== $title) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $data = $form->getData();
                    $comment = $form->get('comment')->getData();
                    $file = $form->get('file')->getData();

                    $payload = [
                        '@id' => $data['@id'],
                        'comment' => $comment,
                        'ccs' => json_encode($data['ccs'], \JSON_THROW_ON_ERROR),
                        'assignee' => $data['assignee']['@id'] ?? $data['assignee'],
                    ];

                    if ($file instanceof UploadedFile) {
                        $payload['file'] = DataPart::fromPath($file->getPathname());
                    }

                    if (\in_array($route, ['trouble_ticket_show', 'trouble_ticket_comment', 'trouble_ticket_send_to_mis', 'trouble_ticket_reopen'], true)) {
                        unset($payload['assignee']);
                    }

                    if ('trouble_ticket_send_to_mis' === $route) {
                        $payload['nullifyAssignee'] = '1';
                    }

                    if ('trouble_ticket_propose_solution' === $route) {
                        switch ($troubleTicket['type']['type']) {
                            case 'Request':
                                if (
                                    \in_array($troubleTicket['status'], [
                                        'IN PROGRESS',
                                        'AWAITING USER',
                                        'PENDING MOO/GKU',
                                        'MOO/GKU AWAITING USER',
                                    ], true)
                                    && \in_array('GG_MIS', $user->getAcls(), true)
                                ) {
                                    $status = 'SOLUTION PROPOSED';
                                    $payload['misAssignee'] = null === $troubleTicket['misAssignee'] ? $user->getIriId() : $troubleTicket['misAssignee']['@id'];
                                    break;
                                }
                                $status = 'MOO/GKU SOLUTION PROPOSED';
                                break;
                            case 'Incident':
                                if (!$troubleTicket['module']['misRelative'] && 'PENDING MOO/GKU' === $troubleTicket['status']) {
                                    $status = 'MOO/GKU SOLUTION PROPOSED';
                                    break;
                                }
                                // no break
                            default:
                                $status = 'SOLUTION PROPOSED';
                                $payload['misAssignee'] = null === $troubleTicket['misAssignee'] ? $user->getIriId() : $troubleTicket['misAssignee']['@id'];
                        }
                        $payload['status'] = $status;
                        $payload['assignee'] = $troubleTicket['createdBy']['@id'];
                    }

                    $formData = new FormDataPart($payload);
                    $this->client->post($url, [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);

                    if ($form instanceof Form) {
                        $button = $form->getClickedButton();
                        if ($form->has('nextTask') && $button === $form->get('nextTask')) {
                            if ($troubleTicket['createdBy']['@id'] !== $user->getIriId() && 'trouble_ticket_comment' !== $request->attributes->get('_route')) {
                                return $this->taskManager->nextTask($request, $troubleTicket, true);
                            }

                            return $this->taskManager->nextTask($request, $troubleTicket);
                        }
                    }

                    return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
                } catch (ClientException $e) {
                    $this->violationMapper->mapToForm($e, $form);
                }
            }
        }
        if (null !== $form && 'transfer to jira' === $title && $this->isGranted('ACL_GG_MIS')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $payload = array_merge($form->getData(), ['troubleTicketId' => $troubleTicket->getIriId()]);

                try {
                    $this->client->save(self::JIRA_ISSUE_URL, $payload);

                    return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
                } catch (ClientException $e) {
                }
            }
        }

        /** @var User $user */
        $user = $this->getUser();
        $subscriptions = $this->client->findBy('subscriptions', ['resource' => $troubleTicket->getIri(), 'user' => $user->getIriId()], []);

        $assigneeForm = $this->createForm(MISAssigneeChoiceType::class, null, [
            'redirect_route_params_extra' => ['id' => $troubleTicket->getIriId()],
            'action' => $this->generateUrl('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]),
        ]);

        return $this->render('mis/trouble_ticket/show.html.twig',
            [
                'canSubscribe' => 0 === $subscriptions->getIterator()->count(),
                'troubleTicket' => $troubleTicket,
                'title' => $title,
                'comments' => $this->client->findBy('/comments', ['resource' => $troubleTicket->getIri(), 'normalization_groups' => ['people_photo']], ['createdAt' => 'DESC']),
                'form' => $form?->createView(),
                'assigneeForm' => $assigneeForm->createView(),
            ]
        );
    }
}
