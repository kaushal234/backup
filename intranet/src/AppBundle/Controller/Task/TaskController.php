<?php

declare(strict_types=1);

namespace AppBundle\Controller\Task;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Mis\ModuleController;
use AppBundle\Controller\Purchasing\WarehouseTask\WarehouseTaskController;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Task\TaskDataTableType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Task\RenewGuestUserType;
use AppBundle\Form\Type\Task\TaskCommentType;
use AppBundle\Manager\FileManager;
use AppBundle\Manager\Task\TaskManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/tasks', defaults: ['alvest_module' => 'TASK', 'moduleDomain' => 'task'])]
class TaskController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    /** @var string */
    public const RESOURCE_URL = 'tasks';
    public const TRANSLATION_DOMAIN = 'task';

    public const string TYPE_TASK = 'task';
    public const string TYPE_PART_NUMBER_TASK = 'partNumberTask';
    public const string TYPE_RENEW_GUEST_USER = 'renewGuestUser';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            ViolationMapper::class,
            FileStreamedResponseFactory::class,
            FileManager::class,
            TaskManager::class,
        ]);
    }

    #[Route(path: '', name: 'task_home', methods: ['GET', 'POST'])]
    #[Route(path: '/search', name: 'task_search', defaults: ['label' => 'Search'], methods: ['GET', 'POST'])]
    #[Template('task/home.html.twig')]
    public function home(Request $request): array|Response
    {
        $client = $this->container->get(Client::class);
        /** @var User $user */
        $user = $this->getUser();

        $path = match ($request->attributes->get('_route')) {
            'task_search' => \sprintf('tasks?%s', http_build_query(['status' => ['IN PROGRESS', 'PENDING']])),
            default => \sprintf('base_tasks?%s', http_build_query(['assignee' => $user->getIriId(), 'status' => ['PENDING', 'IN PROGRESS', 'AWAITING USER', 'SOLUTION PROPOSED', 'PENDING MOO/GKU', 'MOO/GKU SOLUTION PROPOSED', 'MOO/GKU AWAITING USER']])),
        };

        $title = match ($request->attributes->get('_route')) {
            'task_search' => 'Tasks',
            default => 'My tasks and trouble tickets',
        };

        $datatable = $this->createDataTable(TaskDataTableType::class,
            \sprintf('%s', $path), ['title' => $title]
        );
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        $request->getSession()->remove('tasks');

        $myTasks = $client->findBy('base_tasks', ['assignee' => $user->getIriId(), 'status' => ['PENDING', 'PENDING MOO/GKU', 'IN PROGRESS', 'AWAITING USER', 'MOO/GKU AWAITING USER', 'SOLUTION PROPOSED', 'MOO/GKU SOLUTION PROPOSED']]);

        $session = $request->getSession();
        $tasks = $session->get('tasks', []);

        foreach ($myTasks as $value) {
            $tasks[$value['id']] = ['id' => $value['id'], 'type' => $value['@type']];
        }
        $session->set('tasks', $tasks);

        $client = $this->container->get(Client::class);
        $idSearchForm = $this->createForm(IdSearchType::class, null, ['id_label' => false, 'id_placeholder' => 'By ID'])->handleRequest($request);

        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::RESOURCE_URL, $id));

                return $this->redirectToRoute('task_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('task.message.not_exist', ['%id%' => $id], 'task'));
            }
        }

        return [
            'form_id' => $idSearchForm,
            'taskDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/add', name: 'task_add', methods: ['GET', 'POST'])]
    #[Template('task/form/form.html.twig')]
    public function add(Request $request, #[MapQueryParameter] ?string $indiceFactor = null): array
    {
        $client = $this->container->get(Client::class);
        $module = null;
        if (null !== ($moduleName = $request->query->get('module'))) {
            try {
                $modules = $client->findBy(ModuleController::RESOURCE_URL, ['name' => $moduleName]);
                foreach ($modules as $moduleFound) {
                    if ($moduleFound['name'] === $moduleName) {
                        $module = $moduleFound;
                    }
                }
            } catch (ClientException $e) {
                // do nothing, module not found or error, we just don't populate the module
            }
        }

        if (!\in_array($indiceFactor, ['IF 1', 'IF 10', 'IF 100', 'IF 1000', 'IF 10000'], true)) {
            $indiceFactor = null;
        }

        return [
            'type' => 'add',
            'initialState' => [
                'module' => $module ? $module->toArray() : null,
                'referenceId' => $request->query->get('referenceId') ?? null,
                'indiceFactor' => $indiceFactor,
                'confidential' => (bool) $request->query->get('confidential'),
                'disabled' => (bool) $module,
            ],
        ];
    }

    #[Route(path: '/{id}/show', name: 'task_show', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/comment', name: 'task_comment', methods: ['GET|POST'])]
    #[Route(path: '/{id}/reopen', name: 'task_reopen', methods: ['GET|POST'])]
    #[Template('task/show.html.twig')]
    public function show(#[CurrentUser] $user, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_TASK, self::TYPE_PART_NUMBER_TASK, self::TYPE_RENEW_GUEST_USER]])] ApiData $task, Request $request): array|RedirectResponse
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);

        if ('task_reopen' === $request->attributes->get('_route') && !$this->isGranted('TASK_REOPEN_VOTER', $task->getIri())) {
            throw new AccessDeniedHttpException();
        }

        $commentForm = null;
        $route = 'task_comment' === $request->attributes->get('_route') ? 'comment' : 'reopen';

        if ('task_show' !== $request->attributes->get('_route')) {
            $commentForm = $this->createForm(TaskCommentType::class, $task);

            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $data = $commentForm->getData();
                $comment = $commentForm->get('comment')->getData();
                $payload = [
                    'comment' => $comment,
                    'recipients' => json_encode($data['recipients'], \JSON_THROW_ON_ERROR),
                ];

                $file = $commentForm->get('file')->getData();
                if ($file instanceof UploadedFile) {
                    $payload['file'] = DataPart::fromPath($file->getPathname());
                }

                $formData = new FormDataPart($payload);

                try {
                    $client->post(\sprintf('tasks/%d/%s', $task->getIriId(), $route), [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);

                    if ($commentForm instanceof Form) {
                        $button = $commentForm->getClickedButton();
                        if ($commentForm->has('nextTask') && $button === $commentForm->get('nextTask')) {
                            return $this->container->get(TaskManager::class)->nextTask($request, $task);
                        }
                    }

                    return $this->redirectToRoute('task_show', ['id' => $task->getIriId()]);
                } catch (ClientException $exception) {
                    $this->container->get(ViolationMapper::class)->mapToForm($exception, $commentForm);
                }
            }
        }

        $renewGuestUserForm = $this->createForm(RenewGuestUserType::class);
        $renewGuestUserForm->handleRequest($request);
        if ($renewGuestUserForm->isSubmitted()) {
            /** @var ClickableInterface $yesButton */
            $yesButton = $renewGuestUserForm->get('renewalYes');
            /** @var ClickableInterface $noButton */
            $noButton = $renewGuestUserForm->get('renewalNo');
            try {
                if ($yesButton->isClicked() && $renewGuestUserForm->isValid()) {
                    $client->save(\sprintf('tasks/renew_guest_user/%d/accept', $task->getIriId()), [
                        'renewalDurationMonths' => $renewGuestUserForm->get('renewalDurationMonths')->getData(),
                    ]);
                    $this->addFlash('success', $translator->trans('renew_guest_user.success.renewed', [], self::TRANSLATION_DOMAIN));

                    return $this->redirectToRoute('task_show', ['id' => $task->getIriId()]);
                }
                if ($noButton->isClicked()) {
                    $client->save(\sprintf('tasks/renew_guest_user/%d/deny', $task->getIriId()), [
                        'renewalDurationMonths' => $renewGuestUserForm->get('renewalDurationMonths')->getData(),
                    ]);
                    $this->addFlash('success', $translator->trans('renew_guest_user.success.not_renewed', [], self::TRANSLATION_DOMAIN));

                    return $this->redirectToRoute('task_show', ['id' => $task->getIriId()]);
                }
            } catch (ClientException $exception) {
                $this->container->get(ViolationMapper::class)->mapToForm($exception, $renewGuestUserForm);
            }
        }

        return [
            'form' => $commentForm,
            'task' => $task,
            'route' => $route,
            'renewGuestUserForm' => $renewGuestUserForm->createView(),
            ...$this->commentAndSubscribe($task, $user),
        ];
    }

    #[Route(path: '/{id}/pause', name: 'task_pause', methods: ['GET', 'POST'])]
    #[Template('task/show.html.twig')]
    #[IsGranted(attribute: 'FEATURE_PAUSE_UNPAUSE_TASK')]
    public function pause(#[CurrentUser] $user, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_TASK, self::TYPE_PART_NUMBER_TASK]])] ApiData $task, Request $request): array|RedirectResponse
    {
        $client = $this->container->get(Client::class);

        $commentForm = null;
        if ('task_pause' === $request->attributes->get('_route')) {
            $commentForm = $this->createForm(TaskCommentType::class, $task, ['task_pause' => true]);
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $data = $commentForm->getData();
                $comment = $commentForm->get('comment')->getData();

                $payload = [
                    'comment' => $comment,
                ];

                $file = $commentForm->get('file')->getData();
                if ($file instanceof UploadedFile) {
                    $payload['file'] = DataPart::fromPath($file->getPathname());
                }

                $formData = new FormDataPart($payload);

                if ('IN PROGRESS' === $task['status']) {
                    $client->post(\sprintf('tasks/%d/pause', $task->getIriId()), [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);

                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('task.message.pause', [], self::TRANSLATION_DOMAIN));

                    if ($commentForm instanceof Form) {
                        $button = $commentForm->getClickedButton();
                        if ($commentForm->has('nextTask') && $button === $commentForm->get('nextTask')) {
                            return $this->container->get(TaskManager::class)->nextTask($request, $task, true);
                        }
                    }

                    return $this->redirectToRoute('task_show', ['id' => $task->getIriId()]);
                }
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('task.errors.pause', [], self::TRANSLATION_DOMAIN));

                return $this->redirectToRoute('task_show', ['id' => $task->getIriId()]);
            }
        }

        return [
            'form' => $commentForm,
            'task' => $task,
            'route' => 'pause',
            ...$this->commentAndSubscribe($task, $user),
        ];
    }

    #[Route(path: '/{id}/close', name: 'task_close', methods: ['GET', 'POST'])]
    #[Template('task/show.html.twig')]
    #[IsGranted(attribute: 'TASK_WRITE_VOTER', subject: new Expression('args["task"].getIri()'))]
    public function close(#[CurrentUser] $user, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_TASK, self::TYPE_PART_NUMBER_TASK]])] ApiData $task, Request $request): array|RedirectResponse
    {
        $client = $this->container->get(Client::class);

        $commentForm = null;
        if ('task_close' === $request->attributes->get('_route')) {
            $commentForm = $this->createForm(TaskCommentType::class, $task, ['task_close' => true]);
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $comment = $commentForm->get('comment')->getData();
                $payload = [
                    'comment' => $comment,
                ];

                $file = $commentForm->get('file')->getData();
                if ($file instanceof UploadedFile) {
                    $payload['file'] = DataPart::fromPath($file->getPathname());
                }

                $formData = new FormDataPart($payload);

                try {
                    $client->post(\sprintf('tasks/%d/close', $task->getIriId()), [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);
                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('task.message.close', [], self::TRANSLATION_DOMAIN));
                } catch (ClientException $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }

                if ($commentForm instanceof Form) {
                    $button = $commentForm->getClickedButton();
                    if ($commentForm->has('nextTask') && $button === $commentForm->get('nextTask')) {
                        return $this->container->get(TaskManager::class)->nextTask($request, $task, true);
                    }
                }

                return $this->redirectToRoute('task_show', ['id' => $task->getIriId()]);
            }
        }

        return [
            'form' => $commentForm,
            'task' => $task,
            'route' => 'close',
            ...$this->commentAndSubscribe($task, $user),
        ];
    }

    #[Route(path: '/{id}/reschedule', name: 'task_reschedule', methods: ['GET', 'POST'])]
    #[Template('task/show.html.twig')]
    #[IsGranted(attribute: 'TASK_TRANSFER_VOTER', subject: new Expression('args["task"].getIri()'))]
    public function reschedule(#[CurrentUser] $user, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_TASK, self::TYPE_PART_NUMBER_TASK]])] ApiData $task, Request $request): array|RedirectResponse
    {
        $client = $this->container->get(Client::class);

        $commentForm = $this->createForm(TaskCommentType::class, $task, ['task_reschedule' => true]);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $data = $commentForm->getData();
            $comment = $commentForm->get('comment')->getData();

            $payload = [
                '@id' => $data['@id'],
                'rescheduleDate' => $data['rescheduleDate'],
                'recipients' => json_encode($data['recipients'], \JSON_THROW_ON_ERROR),
                'comment' => $comment,
            ];

            $file = $commentForm->get('file')->getData();
            if ($file instanceof UploadedFile) {
                $payload['file'] = DataPart::fromPath($file->getPathname());
            }

            $formData = new FormDataPart($payload);

            $client->post(\sprintf('tasks/%d/reschedule', $task->getIriId()), [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);

            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('task.message.reschedule', [], self::TRANSLATION_DOMAIN));

            if ($commentForm instanceof Form) {
                $button = $commentForm->getClickedButton();
                if ($commentForm->has('nextTask') && $button === $commentForm->get('nextTask')) {
                    return $this->container->get(TaskManager::class)->nextTask($request, $task);
                }
            }

            return $this->redirectToRoute('task_show', ['id' => $task->getIriId()]);
        }

        return [
            'form' => $commentForm,
            'task' => $task,
            'route' => 'reschedule',
            ...$this->commentAndSubscribe($task, $user),
        ];
    }

    #[Route(path: '/{id}/transfer', name: 'task_transfer', methods: ['GET', 'POST'])]
    #[Template('task/show.html.twig')]
    #[IsGranted(attribute: 'TASK_TRANSFER_VOTER', subject: new Expression('args["task"].getIri()'))]
    public function transfer(#[CurrentUser] $user, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_TASK, self::TYPE_PART_NUMBER_TASK]])] ApiData $task, Request $request): array|RedirectResponse
    {
        $client = $this->container->get(Client::class);
        $commentForm = $this->createForm(TaskCommentType::class, $task, ['task_transfer' => true]);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            try {
                $data = $commentForm->getData();

                if (empty($data['assignee'])) {
                    $this->addFlash(
                        'warning',
                        $this->container->get(TranslatorInterface::class)
                            ->trans('task.message.assignee_require', [], self::TRANSLATION_DOMAIN)
                    );

                    return $this->redirectToRoute('task_transfer', ['id' => $task->getIriId()]);
                }

                $payload = [
                    '@id' => $data['@id'],
                    'rescheduleDate' => $data['rescheduleDate'],
                    'assignee' => $data['assignee'],
                    'recipients' => json_encode($data['recipients'], \JSON_THROW_ON_ERROR),
                    'comment' => $commentForm->get('comment')->getData(),
                ];

                $uploadedFile = $commentForm->get('file')->getData();
                if ($uploadedFile instanceof UploadedFile) {
                    $payload['file'] = DataPart::fromPath($uploadedFile->getPathname());
                }

                $payload = array_filter($payload, static fn ($value) => null !== $value);

                $formData = new FormDataPart($payload);

                $client->post(\sprintf('tasks/%d/transfer', $task->getIriId()), [
                    'headers' => $formData->getPreparedHeaders()->toArray(),
                    'body' => $formData->bodyToIterable(),
                ]);

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('task.message.transfer', [], self::TRANSLATION_DOMAIN));

                if ($commentForm instanceof Form) {
                    $button = $commentForm->getClickedButton();
                    if ($commentForm->has('nextTask') && $button === $commentForm->get('nextTask')) {
                        return $this->container->get(TaskManager::class)->nextTask($request, $task);
                    }
                }

                return $this->redirectToRoute('task_show', ['id' => $task->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $commentForm);
            }
        }

        return [
            'form' => $commentForm,
            'task' => $task,
            'route' => 'transfer',
            ...$this->commentAndSubscribe($task, $user),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    #[Template('task/form/form.html.twig')]
    #[IsGranted(attribute: 'TASK_WRITE_VOTER', subject: new Expression('args["task"].getIri()'))]
    public function edit(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_TASK, self::TYPE_PART_NUMBER_TASK]])] ApiData $task): RedirectResponse|array
    {
        if (ucfirst(self::TYPE_PART_NUMBER_TASK) === $task['@type']) {
            return $this->redirectToRoute('part_number_task_edit', ['id' => $task->getIriId()]);
        }

        if (WarehouseTaskController::MODULE_WHT === ($task->module['name'] ?? null)) {
            return $this->redirectToRoute('warehouse_task_edit', ['id' => $task->getIriId()]);
        }

        return [
            'type' => 'edit',
            'initialState' => [
                'taskValues' => $task->toArray(),
                'formType' => 'edit',
            ],
        ];
    }

    #[Route(path: '/{taskId}/files/{id}', name: 'task_files_show', requirements: ['id' => '\d+'], methods: 'GET')]
    public function showFile($taskId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL, $taskId, $id));
    }

    #[Route(path: '/{id}/show_file_ajax', name: 'task_show_file_ajax', methods: ['GET'])]
    #[Template('task/partial/files_ajax.html.twig')]
    public function ajax_list_files(#[ApiValueResolverAttribute] ApiData $task,
    ): array {
        return compact('task');
    }

    #[Route(path: '/{taskId}/file/{fileId}', name: 'task_download_file', methods: ['GET'])]
    public function download_file(int $taskId, int $fileId)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('tasks/%d/files/%d',
            $taskId, $fileId));
    }

    #[Route(path: '/{taskId}/files/{fileId}/delete', name: 'task_delete_file', methods: ['GET'])]
    public function delete_file(int $taskId, int $fileId): RedirectResponse
    {
        $this->container->get(Client::class)->remove(\sprintf('tasks/%d/files', $taskId), $fileId);
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('task.message.delete_file', [], self::TRANSLATION_DOMAIN));

        return $this->redirectToRoute('task_show', ['id' => $taskId]);
    }

    private function commentAndSubscribe(ApiData $task, User $user): array
    {
        $client = $this->container->get(Client::class);
        $comments = $client->findBy('/comments', ['resource' => $task->getIri(), 'normalization_groups' => ['people_photo']], ['createdAt' => 'DESC']);
        $subscriptions = $client->findBy('subscriptions', ['resource' => $task->getIri(), 'user' => $user->getIriId()], []);

        return [
            'comments' => $comments,
            'canSubscribe' => 0 === $subscriptions->getIterator()->count(),
        ];
    }
}
