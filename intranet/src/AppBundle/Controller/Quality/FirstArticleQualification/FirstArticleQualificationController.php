<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality\FirstArticleQualification;

use ActivityBundle\Form\Type\CommentType;
use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Quality\FirstArticleQualification\FirstArticleQualificationFilterType;
use AppBundle\Form\Type\Common\FileEditDescriptionType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Quality\FirstArticleQualification\FirstArticleQualificationType;
use AppBundle\Form\Type\Quality\FirstArticleQualification\PlanDuplicateType;
use AppBundle\Form\Type\Quality\FirstArticleQualification\PlanType;
use AppBundle\Manager\Quality\FirstArticleQualification\FirstArticleQualificationStatus;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/first-article-qualifications', defaults: ['alvest_module' => 'FAQ', 'moduleDomain' => 'first_article_qualifications'])]
class FirstArticleQualificationController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const RESOURCE_URL = 'quality/first_article_qualifications';
    final public const TRANSLATION_DOMAIN = 'first_article_qualification';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, CsvStreamedResponseFactory::class, FileStreamedResponseFactory::class, Security::class]);
    }

    #[Route(path: '/search', name: 'first_article_qualifications_search', defaults: ['page' => 1, 'itemsPerPage' => 10])]
    #[Template('/quality/first_article_qualification/filters.html.twig')]
    public function search(Request $request)
    {
        $client = $this->container->get(Client::class);
        $user = $client->get('me');

        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'by_id',
            'csrf_protection' => false,
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            try {
                $id = $idSearchForm->get('id')->getData();
                $client->get(self::RESOURCE_URL.'/'.$id);

                return $this->redirectToRoute('first_article_qualifications_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.messages.not_found', [], self::TRANSLATION_DOMAIN));
            }
        }

        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', static::itemsPerPage);
        $parameters['normalization_groups'] = ['faq_progress'];

        if ($request->query->has('exists')) {
            $parameters['exists'] = $request->query->all('exists');
            $request->query->remove('exists');
        }

        $formFilters = $this->container->get('form.factory')->createNamed('', FirstArticleQualificationFilterType::class, [], [
            'action' => $this->generateUrl('first_article_qualifications_search'),
            'method' => 'GET',
        ]);

        if (\in_array('ALL', $request->query->all('status'), true)) {
            $request->query->remove('status');
        }
        if ('ALL' === $request->query->get('planApprovalStatus')) {
            $request->query->remove('planApprovalStatus');
        }

        $formFilters->handleRequest($request);

        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());

            if (!$parameters['noOpenTasks']) {
                unset($parameters['noOpenTasks']);
            }
            if (!$parameters['noPlan']) {
                unset($parameters['noPlan']);
            }
            if ($formFilters->getClickedButton() && 'xls' === $formFilters->getClickedButton()->getName()) {
                $parameters['normalizationGroupsOverride'] = ['faq_export'];
                $parameters['itemsPerPage'] = 1000;
                $parameters['context'] = ['datetime_format' => 'Y-m-d'];
                unset($parameters['normalization_groups']);

                return $this->container->get(CsvStreamedResponseFactory::class)->create(self::RESOURCE_URL, $parameters);
            }
        }

        try {
            $firstArticleQualifications = $client->findBy(self::RESOURCE_URL, $parameters, ['createdAt' => 'desc']);
        } catch (ClientException $e) {
            $this->container->get(ViolationMapper::class)->mapToForm($e, $formFilters);
            $firstArticleQualifications = [];
        }

        return [
            'user' => $user,
            'idSearchForm' => $idSearchForm->createView(),
            'firstArticleQualifications' => $firstArticleQualifications,
            'formFilters' => $formFilters->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'currentPage' => $parameters['page'],
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '', name: 'first_article_qualifications_home', methods: 'GET')]
    #[Template('quality\first_article_qualification\dashboard.html.twig')]
    public function dashboard()
    {
        $client = $this->container->get(Client::class);

        return [
            'statusByBusinessUnit' => $client->get('reports/resource=/quality/first_article_qualifications;x=location.name;y=status'),
            'planStatusByBusinessUnit' => $client->get('reports/resource=/quality/first_article_qualifications;x=location.name;y=planApprovalStatus'),
        ];
    }

    #[Route(path: '/add', name: 'first_article_qualifications_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'first_article_qualifications_edit', methods: ['GET', 'POST'])]
    #[Template('quality/first_article_qualification/write.html.twig')]
    public function write(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $firstArticleQualification = null)
    {
        $isEditMode = null !== $firstArticleQualification;

        if ($isEditMode) {
            $status = $firstArticleQualification['status'] ?? null;
            $resourceId = $firstArticleQualification['@id'] ?? null;

            if ('QUALIFIED' === $status && !$this->isGranted('FEATURE_FIRST_ARTICLE_QUALIFICATION_EDIT_QUALIFIED')) {
                throw new AccessDeniedException();
            }

            if (!\in_array($status, ['REJECTED', 'CONDITIONAL'], true) && !$this->isGranted('FEATURE_FAQ_PLAN_WRITE', $resourceId)) {
                throw new AccessDeniedException();
            }
        } else {
            $partNumbers = $request->query->all('partNumbers');

            if (empty($partNumbers)) {
                $partNumbersData = [
                    ['number' => '', 'revision' => '', 'description' => ''],
                ];
            } else {
                $partNumbersData = array_map(
                    static fn ($pn) => ['number' => $pn, 'revision' => '', 'description' => ''],
                    $partNumbers
                );
            }

            $firstArticleQualification = [
                'planDefinitionDueDate' => (new \DateTime('+7 days'))->format('Y-m-d\TH:i:sO'),
                'dueDate' => (new \DateTime('+21 days'))->format('Y-m-d\TH:i:sO'),
                'eap' => $request->query->get('eap'),
                'meap' => $request->query->get('meap'),
                'partNumbers' => $partNumbersData,
            ];
        }

        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $form = $this->createForm(FirstArticleQualificationType::class, $firstArticleQualification)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rawData = $form->getData();
            $rawData = $rawData instanceof ApiData ? $rawData->toArray() : $rawData;

            $data = array_intersect_key($rawData, array_flip([
                '@id',
                'location',
                'buyer',
                'owner',
                'supplierNumber',
                'iFactor',
                'planDefinitionDueDate',
                'dueDate',
                'eap',
                'meap',
                'tags',
                'members',
                'partNumbers',
                'equipmentRecords',
                'productFamily',
            ]));
            try {
                $client = $this->container->get(Client::class);
                $faq = $client->save(self::RESOURCE_URL, $data);

                $flashMessage = $isEditMode
                    ? 'first_article_qualification.messages.successfully_updated'
                    : 'first_article_qualification.messages.successfully_created';

                $this->addFlash('success', $translator->trans($flashMessage, [], 'first_article_qualification'));

                return $this->redirectToRoute('first_article_qualifications_show', ['id' => Iri::id($faq)]);
            } catch (ClientException $exception) {
                $violationMapper->mapToForm($exception, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'firstArticleQualification' => $firstArticleQualification,
            'sequenceId' => $request->query->get('sequenceId'),
            'title' => $isEditMode ? 'first_article_qualification.titles.edit' : 'first_article_qualification.titles.add',
        ];
    }

    #[Route(path: '/{id}/show', name: 'first_article_qualifications_show', methods: 'GET')]
    #[Template('quality\first_article_qualification\show.html.twig')]
    public function firstArticleQualificationShow(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'filters' => ['normalization_groups' => ['people_photo', 'file:light']]])] ApiData $firstArticleQualification)
    {
        $client = $this->container->get(Client::class);
        $user = $client->get('me');
        $plan = $firstArticleQualification['plan'];
        $completedCount = 0;
        foreach ($plan as $planItem) {
            if (100 === $planItem['completionRate']) {
                ++$completedCount;
            }
        }
        $percentageOfProgress = empty($plan) ? 0 : (int) ($completedCount / \count($plan) * 100);

        $comments = $client->findBy('comments', ['resource' => $firstArticleQualification->getIri(), 'normalization_groups' => ['people_photo', 'file:light']])->getSimpleArrayCopy();

        foreach ($firstArticleQualification['files'] as $file) {
            $comments[] = [
                'message' => \sprintf(
                    '<i class="fa fa-file"></i>&nbsp;<a href="%s">%s</a>',
                    $this->generateUrl('first_article_qualification_files_show', [
                        'faqId' => $firstArticleQualification->getIriId(),
                        'id' => $file['id'],
                    ]),
                    $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.files.new_file', ['%extension%' => $file['extension']], self::TRANSLATION_DOMAIN)
                ),
                'user' => $file['poster'],
                'createdAt' => $file['createdAt'],
            ];
        }

        usort($comments, static function ($comment1, $comment2) {
            if ($comment1['createdAt'] === $comment2['createdAt']) {
                return 0;
            }

            return $comment1['createdAt'] > $comment2['createdAt'] ? -1 : 1;
        });

        return [
            'user' => $user,
            'comments' => $comments,
            'allStatus' => FirstArticleQualificationStatus::getStatuses(),
            'firstArticleQualification' => $firstArticleQualification,
            'commentForm' => $this->container->get('form.factory')->createNamed('comment', CommentType::class)->createView(),
            'percentageOfProgress' => $percentageOfProgress,
        ];
    }

    #[Route(path: '/{id}/status/{status}', name: 'first_article_qualifications_status', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'FAQ_STATUS_VOTER', subject: new Expression('args["firstArticleQualification"].getIri()'))]
    public function changeFirstArticleQualificationStatus(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $firstArticleQualification, $status): RedirectResponse
    {
        try {
            $response = $this->container->get(Client::class)->put(self::RESOURCE_URL.\sprintf('/%s/status', $firstArticleQualification->getIriId()), [
                'json' => [
                    '@id' => $firstArticleQualification->getIri(),
                    'status' => $status,
                ],
            ]);
            if (isset($response['status']) && $response['status'] === $status) {
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.messages.status.success', [], 'first_article_qualification')
                );
            }
        } catch (ClientException $e) {
            $form = $this->createFormBuilder()->getForm();
            $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            $messages = [];
            foreach ($form->getErrors() as $error) {
                /* @var FormError $error */
                $messages[] = $error->getMessage();
            }
            $this->addFlash(
                'error',
                [] !== $messages ? implode('<br/>', $messages) : $e->getMessage()
            );
        }

        return $this->redirectToRoute('first_article_qualifications_show', ['id' => $firstArticleQualification->getIriId()]);
    }

    #[Route(path: '/{id}/plan-status/{planStatus}', name: 'first_article_qualifications_plan_status', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'FEATURE_FAQ_PLAN_APPROVAL', subject: new Expression('args["firstArticleQualification"].getIri()'))]
    public function changePlanStatus(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $firstArticleQualification, $planStatus): RedirectResponse
    {
        try {
            $response = $this->container->get(Client::class)->save(
                self::RESOURCE_URL.\sprintf('/%s', $firstArticleQualification->getIriId()),
                ['@id' => $firstArticleQualification->getIri(),
                    'planApprovalStatus' => $planStatus,
                ]
            );

            if (isset($response['planApprovalStatus']) && $response['planApprovalStatus'] === $planStatus) {
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.messages.plan_status', [], self::TRANSLATION_DOMAIN)
                );
            }
        } catch (ClientException $e) {
            $form = $this->createFormBuilder()->getForm();
            $this->container->get(ViolationMapper::class)->mapToForm($e, $form);

            $messages = [];
            foreach ($form->getErrors() as $error) {
                /* @var FormError $error */
                $messages[] = $error->getMessage();
            }
            $this->addFlash(
                'error',
                [] !== $messages ? implode('<br/>', $messages) : $e->getMessage()
            );
        }

        return $this->redirectToRoute('first_article_qualifications_plan', ['id' => $firstArticleQualification->getIriId()]);
    }

    #[Route(path: '/{id}/files', name: 'first_article_qualification_files', methods: 'GET')]
    #[Template('quality/first_article_qualification/files.html.twig')]
    public function firstArticleQualificationFiles(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $firstArticleQualification)
    {
        return ['firstArticleQualification' => $firstArticleQualification];
    }

    #[Route(path: '/{id}/files_ajax', name: 'first_article_qualification_files_ajax', methods: 'GET')]
    #[Template('quality/first_article_qualification/files_ajax.html.twig')]
    public function firstArticleQualificationFilesAjax(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $firstArticleQualification)
    {
        return ['firstArticleQualification' => $firstArticleQualification];
    }

    #[Route(path: '/{faqId}/files/{id}', name: 'first_article_qualification_files_show', methods: 'GET')]
    public function showFile($faqId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL, $faqId, $id));
    }

    #[Route(path: '/{faqId}/files/{id}/delete', name: 'delete_first_article_qualification_file', methods: ['GET'])]
    public function deleteFile(Request $request, $faqId, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_faq_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('first_article_qualification_files', ['id' => $faqId]);
        }
        $operation = \sprintf('files/%s', $id);
        try {
            $this->container->get(Client::class)->request(self::RESOURCE_URL, $faqId, $operation, Request::METHOD_DELETE);
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.messages.error.delete_file', [], self::TRANSLATION_DOMAIN)
            );
        }

        return $this->redirectToRoute('first_article_qualification_files', ['id' => $faqId]);
    }

    #[Route(path: '/{faqId}/files/{id}/edit', name: 'first_article_qualification_files_edit', requirements: ['id' => '\d+', 'faqId' => '\d+'], methods: ['GET', 'POST'])]
    public function editFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'faqId'])] ApiData $firstArticleQualification, int $id): Response
    {
        $file = array_filter($firstArticleQualification['files'], static function (array $file) use ($id) {
            return $id === $file['id'];
        });
        if (1 !== \count($file)) {
            return $this->redirectToRoute('first_article_qualification_files', ['id' => $firstArticleQualification->getIriId()]);
        }
        $file = current($file);
        $form = $this->container->get('form.factory')->createNamed('editFile', FileEditDescriptionType::class, $file);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $this->container->get(Client::class)->save('files', [
                    '@id' => \sprintf('/files/%d', $id),
                    'description' => $data['description'],
                ]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.messages.success.file_description', [], self::TRANSLATION_DOMAIN)
            );

            return $this->redirectToRoute('first_article_qualification_files', ['id' => $firstArticleQualification->getIriId()]);
        }

        return $this->render('quality/first_article_qualification/files_edition.html.twig', [
            'form' => $form->createView(),
            'firstArticleQualification' => $firstArticleQualification,
        ]);
    }

    #[Route(path: '/{id}/logs', name: 'first_article_qualification_logs', methods: 'GET')]
    #[Template('quality/first_article_qualification/logs.html.twig')]
    public function firstArticleQualificationLogs(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $firstArticleQualification)
    {
        return ['firstArticleQualification' => $firstArticleQualification];
    }

    #[Route(path: '/{id}/delete', name: 'first_article_qualification_delete', methods: 'GET')]
    public function delete($id, Request $request): RedirectResponse
    {
        $translator = $this->container->get(TranslatorInterface::class);
        if (!$this->isCsrfTokenValid('delete_faq', $request->query->get('_token'))) {
            $this->addFlash('error', $translator->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('first_article_qualifications_show', ['id' => $id]);
        }
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $id);

            $this->addFlash(
                'success',
                $translator->trans('first_article_qualification.messages.success.delete', [], self::TRANSLATION_DOMAIN)
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $translator->trans('first_article_qualification.messages.error.delete', [], self::TRANSLATION_DOMAIN)
            );
        }

        return $this->redirectToRoute('first_article_qualifications_home');
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route(path: '/{id}/plan', name: 'first_article_qualifications_plan', methods: ['GET', 'POST'])]
    #[Template('quality/first_article_qualification/plan.html.twig')]
    public function plan(int $id, Request $request): array|Response
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);

        $firstArticleQualification = $client->find(self::RESOURCE_URL, $id, ['raw_results' => true]);
        $planItemTypes = $client->findBy('quality/plan_item_types', [], [], ['raw_results' => true])['hydra:member'];

        $editable = $this->isGranted('FEATURE_FAQ_PLAN_WRITE', $firstArticleQualification['@id'])
            && 'APPROVED' !== $firstArticleQualification['planApprovalStatus'];

        $completionEditable = $this->isGranted('FEATURE_FAQ_PLAN_WRITE', $firstArticleQualification['@id']);

        $form = $this->createForm(PlanType::class, [
            'plan' => $firstArticleQualification['plan'] ?? [],
        ], [
            'plan_item_types' => $planItemTypes,
            'plan_editable' => $editable,
            'plan_completion_editable' => $completionEditable,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payload = [
                '@id' => $firstArticleQualification['@id'],
                'plan' => $this->buildPlanPayload($form->get('plan')->getData() ?? [], $planItemTypes),
            ];

            try {
                $client->save('quality/first_article_qualifications', $payload);

                $this->addFlash('success', $translator->trans('first_article_qualification.messages.success.plan_save', [], self::TRANSLATION_DOMAIN));

                return $this->redirectToRoute('first_article_qualifications_plan', ['id' => $id]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        $duplicateForm = $this->createForm(PlanDuplicateType::class, null, [
            'location' => $firstArticleQualification['location']['@id'] ?? null,
            'excludeId' => $id,
            'action' => $this->generateUrl('first_article_qualifications_plan_duplicate', ['id' => $id]),
        ]);

        return [
            'firstArticleQualification' => $firstArticleQualification,
            'form' => $form->createView(),
            'duplicateForm' => $duplicateForm->createView(),
            'plan_item_types' => $planItemTypes,
            'editable' => $editable,
            'completion_editable' => $completionEditable,
        ];
    }

    #[Route(path: '/{id}/plan/duplicate', name: 'first_article_qualifications_plan_duplicate', methods: ['POST'])]
    public function planDuplicate(int $id, Request $request): Response
    {
        $client = $this->container->get(Client::class);
        $firstArticleQualification = $client->find(self::RESOURCE_URL, $id, ['raw_results' => true]);
        $translator = $this->container->get(TranslatorInterface::class);

        $form = $this->createForm(PlanDuplicateType::class, null, [
            'location' => $firstArticleQualification['location']['@id'] ?? null,
            'excludeId' => $id,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $targets = $form->get('targets')->getData();

            try {
                $response = $client->post('/quality/first_article_qualifications/duplicate_plan', [
                    'json' => [
                        'source' => $firstArticleQualification['@id'],
                        'targets' => $targets,
                    ],
                ]);

                $links = array_map(
                    fn ($target) => \sprintf(
                        '<a href="%s">FAQ#%d</a>',
                        $this->generateUrl('first_article_qualifications_show', ['id' => $id = (int) basename((string) $target)]),
                        $id
                    ),
                    $response['duplicatedFor'] ?? $targets
                );

                $this->addFlash('success', $translator->trans(
                    'first_article_qualification.messages.success.duplication',
                    ['%links%' => implode(', ', $links)],
                    self::TRANSLATION_DOMAIN,
                ));
            } catch (ClientExceptionInterface $e) {
                $payload = json_decode($e->getResponse()->getContent(false), true) ?: [];

                $this->addFlash('danger', $payload['hydra:description']
                    ?? $payload['detail']
                    ?? $translator->trans('first_article_qualification.messages.error.duplication', [], self::TRANSLATION_DOMAIN));
            }
        }

        return $this->redirectToRoute('first_article_qualifications_plan', ['id' => $id]);
    }

    /**
     * Transform form lines in required payload for API.
     */
    private function buildPlanPayload(array $lines, array $planItemTypes): array
    {
        $typesByIri = array_column($planItemTypes, null, '@id');

        return array_map(static function (array $line) use ($typesByIri): array {
            $type = $typesByIri[$line['type']] ?? null;
            $priorAllowed = (bool) ($type['requestablePriorDelivery'] ?? false);
            $purchaseAllowed = (bool) ($type['requestableAtPurchaseOrder'] ?? false);

            $item = [
                'type' => $line['type'],
                'description' => $line['description'] ?? '',
                'comment' => $line['comment'] ?? '',
                'completionRate' => (int) $line['completionRate'],
                'requestedPriorDelivery' => $priorAllowed ? (bool) ($line['requestedPriorDelivery'] ?? false) : null,
                'requestedAtPurchaseOrder' => $purchaseAllowed ? (bool) ($line['requestedAtPurchaseOrder'] ?? false) : null,
            ];

            if (!empty($line['id'])) {
                $item['@id'] = '/quality/first_article_qualifications_plan_items/'.$line['id'];
            }

            return $item;
        }, $lines);
    }
}
