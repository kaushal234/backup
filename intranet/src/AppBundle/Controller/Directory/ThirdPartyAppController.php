<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Mis\ThirdPartyApp\UpdateTaskController;
use AppBundle\DataTable\Type\Directory\ProfileUpdateTaskDataTableType;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\UpdateTaskConfirmationType;
use AppBundle\Manager\Mis\ThirdPartyApp\MemberManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ThirdPartyAppController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            ViolationMapper::class,
            MemberManager::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(path: '/directory/people/{id}/third_party_apps', name: 'directory_people_third_party_app', methods: 'GET')]
    #[Template('directory/people/third_party_app/index.html.twig')]
    public function index(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people,
        Request $request,
    ) {
        $updateTasksDataTable = $this->createDataTable(
            ProfileUpdateTaskDataTableType::class,
            ProfileUpdateTaskDataTableType::RESOURCE,
            ['user' => $people->getIri()],
        );
        if ($updateTasksDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($updateTasksDataTable);
        }

        $client = $this->container->get(Client::class);

        $thirdAppAccess = $client->findBy('modules/third_party_app/members', [
            'user' => $people->getIriId(),
        ]);

        $updateTasks = [];
        $updateTasksInProgress = [];
        if ($this->isGranted('FEATURE_MODULE_WRITE')) {
            $updateTasksInProgress = $client->findBy('modules/third_party_app/update_tasks', [
                'user' => $people->getIriId(),
                'done' => false,
            ]);
            $updateTasks = $client->findBy('modules/third_party_app/update_tasks', [
                'user' => $people->getIriId(),
            ]);
        }

        $updateTasksDataTable->handleRequest($request);

        return [
            'people' => $people,
            'thirdAppAccessList' => $thirdAppAccess,
            'updateTasks' => $updateTasks,
            'updateTasksDataTable' => $updateTasksDataTable->createView(),
            'numberOfTasksInProgress' => $updateTasksInProgress ? $updateTasksInProgress->getIterator()->count() : 0,
        ];
    }

    #[Route(path: '/directory/people/{id}/third_party_apps/sync_tasks', name: 'directory_people_third_party_app_sync_tasks', methods: 'GET|POST')]
    #[IsGranted('FEATURE_MODULE_WRITE')]
    public function syncUpdateTasks(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'people', 'filters' => ['normalization_groups' => ['application_member']]])] ApiData $people)
    {
        try {
            $this->container->get(Client::class)->post(\sprintf('modules/third_party_app/sync_update_tasks/%d', $people->getIriId()), ['json' => []]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.success.sync', [], 'mis'));
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.error.sync', [], 'mis'));
        }

        return $this->redirectToRoute('directory_people_third_party_app', ['id' => $people->getIriId()]);
    }

    #[Route(path: '/directory/people/{id}/third_party_apps/delete/{memberId}', name: 'directory_people_third_party_app_delete', methods: 'GET')]
    #[IsGranted('FEATURE_MODULE_WRITE')]
    public function delete(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people, int $memberId): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_application', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete third_party_app: please refresh your form.');

            return $this->redirectToRoute('directory_people_third_party_app', ['id' => $people->getIriId()]);
        }

        try {
            $this->container->get(MemberManager::class)->remove($memberId);
            $this->addFlash('success', 'New update task created');
        } catch (ClientException $e) {
            $this->addFlash('error', 'Cannot delete third party third_party_app: '.$e->getMessage());
        }

        return $this->redirectToRoute('directory_people_third_party_app', ['id' => $people->getIriId()]);
    }

    #[Route(path: '/directory/people/{id}/third_party_apps/{updateTaskId}/grant_access_accepted', name: 'directory_people_third_party_app_grant_access_accepted', methods: 'POST')]
    public function grantAccessAccepted(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people,
        #[ApiValueResolverAttribute(parameters: ['resource' => UpdateTaskController::RESOURCE_URL, 'id' => 'updateTaskId'])] ApiData $updateTask,
        Request $request,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$updateTask['thirdPartyApp']['id']);

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Extract before acceptGrantAccess() mutates the object
                $userIri = $updateTask['user']['@id'];
                $moduleIri = $updateTask['thirdPartyApp']['@id'];

                $this->container->get(MemberManager::class)->acceptGrantAccess($updateTask);

                $this->container->get(Client::class)->post('modules/blacklist/move_from', [
                    'json' => [
                        'user' => $userIri,
                        'module' => $moduleIri,
                    ],
                ]);

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.success.grant_access_accepted', [], 'mis'));
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.error.grant_access_accepted', [], 'mis'));
            }
        }

        return $this->redirectToRoute('directory_people_third_party_app', [
            'id' => $people->getIriId(),
            '_fragment' => 'updateTasksList',
        ]);
    }

    #[Route(path: '/directory/people/{id}/third_party_apps/{updateTaskId}/grant_access_denied', name: 'directory_people_third_party_app_grand_access_denied', methods: 'POST')]
    public function grantAccessDenied(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people,
        #[ApiValueResolverAttribute(parameters: ['resource' => UpdateTaskController::RESOURCE_URL, 'id' => 'updateTaskId'])] ApiData $updateTask,
        Request $request,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$updateTask['thirdPartyApp']['id']);

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(MemberManager::class)->denyGrantAccess($updateTask);
                $this->container->get(Client::class)->post('modules/blacklist/move',
                    [
                        'json' => [
                            'user' => $updateTask['user'],
                            'module' => $updateTask['thirdPartyApp'],
                        ],
                    ]
                );

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.success.grant_access_denied', [], 'mis'));
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.error.grant_access_denied', [], 'mis'));
            }
        }

        return $this->redirectToRoute('directory_people_third_party_app', [
            'id' => $people->getIriId(),
            '_fragment' => 'updateTasksList',
        ]);
    }

    #[Route(path: '/directory/people/{id}/third_party_apps/{updateTaskId}/remove_access_accepted', name: 'directory_people_third_party_app_remove_access_accepted', methods: 'POST')]
    public function removeAccessAccepted(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people,
        #[ApiValueResolverAttribute(parameters: ['resource' => UpdateTaskController::RESOURCE_URL, 'id' => 'updateTaskId'])] ApiData $updateTask,
        Request $request,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$updateTask['thirdPartyApp']['id']);

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(MemberManager::class)->acceptRemoveAccess($updateTask);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.success.remove_access_accepted', [], 'mis'));
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.error.remove_access_accepted', [], 'mis'));
            }
        }

        return $this->redirectToRoute('directory_people_third_party_app', [
            'id' => $people->getIriId(),
            '_fragment' => 'updateTasksList',
        ]);
    }

    #[Route(path: '/directory/people/{id}/third_party_apps/{updateTaskId}/remove_access_denied', name: 'directory_people_third_party_app_remove_access_denied', methods: 'POST')]
    public function removeAccessDenied(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people,
        #[ApiValueResolverAttribute(parameters: ['resource' => UpdateTaskController::RESOURCE_URL, 'id' => 'updateTaskId'])] ApiData $updateTask,
        Request $request,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$updateTask['thirdPartyApp']['id']);

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(MemberManager::class)->denyRemoveAccess($updateTask);
                $this->container->get(Client::class)->post('modules/whitelist/move',
                    [
                        'json' => [
                            'user' => $updateTask['user'],
                            'module' => $updateTask['thirdPartyApp'],
                        ],
                    ]
                );
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.success.remove_access_denied', [], 'mis'));
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.error.remove_access_denied', [], 'mis'));
            }
        }

        return $this->redirectToRoute('directory_people_third_party_app', [
            'id' => $people->getIriId(),
            '_fragment' => 'updateTasksList',
        ]);
    }

    #[Route(path: '/directory/people/{id}/update_tasks/{updateTaskId}/denied_modal', name: 'directory_people_update_tasks_denied_modal', methods: 'GET')]
    public function deniedModal(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])]
        ApiData $people,
        #[ApiValueResolverAttribute(parameters: ['resource' => UpdateTaskController::RESOURCE_URL, 'id' => 'updateTaskId'])]
        ApiData $updateTask,
    ): Response {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$updateTask['thirdPartyApp']['id']);

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);

        $route = match ($updateTask['demandType']) {
            'GRANT_ACCESS' => 'directory_people_third_party_app_grand_access_denied',
            'REMOVE_ACCESS' => 'directory_people_third_party_app_remove_access_denied',
            default => throw new \UnhandledMatchError($updateTask['demandType']),
        };

        $labelDescription = match ($updateTask['demandType']) {
            'GRANT_ACCESS' => 'mis.update_task.confirm_modal.grant_denied.target.message',
            'REMOVE_ACCESS' => 'mis.update_task.confirm_modal.remove_denied.target.message',
            default => throw new \UnhandledMatchError($updateTask['demandType']),
        };

        $labelConfirm = match ($updateTask['demandType']) {
            'GRANT_ACCESS' => 'mis.update_task.confirm_modal.grant_denied.target.button',
            'REMOVE_ACCESS' => 'mis.update_task.confirm_modal.remove_denied.target.button',
            default => throw new \UnhandledMatchError($updateTask['demandType']),
        };

        return $this->render('directory/people/third_party_app/modal/confirmation.html.twig', [
            'module' => $updateTask['thirdPartyApp'],
            'user' => $people,
            'updateTask' => $updateTask,
            'route' => $route,
            'labelDescription' => $labelDescription,
            'labelConfirm' => $labelConfirm,
            'type' => 'danger',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/update_tasks/{updateTaskId}/confirmed_modal', name: 'directory_people_update_tasks_confirmed_modal', methods: 'GET')]
    public function confirmedModal(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])]
        ApiData $people,
        #[ApiValueResolverAttribute(parameters: ['resource' => UpdateTaskController::RESOURCE_URL, 'id' => 'updateTaskId'])]
        ApiData $updateTask,
    ): Response {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$updateTask['thirdPartyApp']['id']);

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);

        $route = match ($updateTask['demandType']) {
            'GRANT_ACCESS' => 'directory_people_third_party_app_grant_access_accepted',
            'REMOVE_ACCESS' => 'directory_people_third_party_app_remove_access_accepted',
            default => throw new \UnhandledMatchError($updateTask['demandType']),
        };

        $labelDescription = match ($updateTask['demandType']) {
            'GRANT_ACCESS' => 'mis.update_task.confirm_modal.grant_confirmed.target.message',
            'REMOVE_ACCESS' => 'mis.update_task.confirm_modal.remove_confirmed.target.message',
            default => throw new \UnhandledMatchError($updateTask['demandType']),
        };

        $labelConfirm = match ($updateTask['demandType']) {
            'GRANT_ACCESS' => 'mis.update_task.confirm_modal.grant_confirmed.target.button',
            'REMOVE_ACCESS' => 'mis.update_task.confirm_modal.remove_confirmed.target.button',
            default => throw new \UnhandledMatchError($updateTask['demandType']),
        };

        return $this->render('directory/people/third_party_app/modal/confirmation.html.twig', [
            'module' => $updateTask['thirdPartyApp'],
            'user' => $people,
            'updateTask' => $updateTask,
            'route' => $route,
            'labelDescription' => $labelDescription,
            'labelConfirm' => $labelConfirm,
            'type' => 'danger',
            'form' => $form->createView(),
        ]);
    }
}
