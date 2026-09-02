<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\ThirdPartyApp;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Mis\ModuleController;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\UpdateTaskConfirmationType;
use AppBundle\Manager\Mis\ThirdPartyApp\MemberManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/modules', defaults: ['alvest_module' => 'MOD', 'breadcrumb_label' => 'menu.modules.title', 'moduleDomain' => 'mis_modules'])]
class UpdateTaskController extends AbstractController
{
    public const string RESOURCE_URL = 'modules/third_party_app/update_tasks';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            MemberManager::class,
            TranslatorInterface::class,
            Client::class,
        ]);
    }

    #[Route(path: '/{id}/update_tasks/{updateTaskId}/denied_modal', name: 'mis_third_party_app_update_tasks_denied_modal', methods: 'GET')]
    public function deniedModal(
        #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])]
        ApiData $module,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'updateTaskId'])]
        ApiData $updateTask,
    ): Response {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);

        $route = match ($updateTask['demandType']) {
            'GRANT_ACCESS' => 'mis_third_party_app_update_tasks_grand_access_denied',
            'REMOVE_ACCESS' => 'mis_third_party_app_update_tasks_remove_access_denied',
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

        return $this->render('mis/modules/third_party_app/update_tasks/partial/modal/confirmation.html.twig', [
            'module' => $module,
            'updateTask' => $updateTask,
            'route' => $route,
            'labelDescription' => $labelDescription,
            'labelConfirm' => $labelConfirm,
            'type' => 'danger',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/update_tasks/{updateTaskId}/confirmed_modal', name: 'mis_third_party_app_update_tasks_confirmed_modal', methods: 'GET')]
    public function confirmedModal(
        #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])]
        ApiData $module,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'updateTaskId'])]
        ApiData $updateTask,
    ): Response {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);

        $route = match ($updateTask['demandType']) {
            'GRANT_ACCESS' => 'mis_third_party_app_update_tasks_grant_access_accepted',
            'REMOVE_ACCESS' => 'mis_third_party_app_update_tasks_remove_access_accepted',
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

        return $this->render('mis/modules/third_party_app/update_tasks/partial/modal/confirmation.html.twig', [
            'module' => $module,
            'updateTask' => $updateTask,
            'route' => $route,
            'labelDescription' => $labelDescription,
            'labelConfirm' => $labelConfirm,
            'type' => 'info',
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/update_tasks/{updateTaskId}/grant_access_accepted', name: 'mis_third_party_app_update_tasks_grant_access_accepted', methods: 'POST')]
    public function grantAccessAccepted(
        #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])]
        ApiData $module,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'updateTaskId'])]
        ApiData $updateTask,
        Request $request,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(MemberManager::class)->acceptGrantAccess($updateTask);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.success.grant_access_accepted', [], 'mis'));
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.error.grant_access_accepted', [], 'mis'));
            }
        }

        return $this->redirectToRoute('mis_third_party_app_update_tasks', ['id' => $module->getIriId()]);
    }

    #[Route(path: '/{id}/update_tasks/{updateTaskId}/grant_access_denied', name: 'mis_third_party_app_update_tasks_grand_access_denied', methods: 'POST')]
    public function grantAccessDenied(
        #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])]
        ApiData $module,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'updateTaskId'])]
        ApiData $updateTask,
        Request $request,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(MemberManager::class)->denyGrantAccess($updateTask);
                $this->container->get(Client::class)->post('modules/blacklist/move',
                    [
                        'json' => [
                            'user' => $updateTask['user'],
                            'module' => $module->getIri(),
                        ],
                    ]
                );

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.success.grant_access_denied', [], 'mis'));
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.error.grant_access_denied', [], 'mis'));
            }
        }

        return $this->redirectToRoute('mis_third_party_app_update_tasks', ['id' => $module->getIriId()]);
    }

    #[Route(path: '/{id}/update_tasks/{updateTaskId}/remove_access_accepted', name: 'mis_third_party_app_update_tasks_remove_access_accepted', methods: 'POST')]
    public function removeAccessAccepted(
        #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])]
        ApiData $module,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'updateTaskId'])]
        ApiData $updateTask,
        Request $request,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

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

        return $this->redirectToRoute('mis_third_party_app_update_tasks', ['id' => $module->getIriId()]);
    }

    #[Route(path: '/{id}/update_tasks/{updateTaskId}/remove_access_denied', name: 'mis_third_party_app_update_tasks_remove_access_denied', methods: 'POST')]
    public function removeAccessDenied(
        #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])]
        ApiData $module,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'updateTaskId'])]
        ApiData $updateTask,
        Request $request,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->createForm(UpdateTaskConfirmationType::class, $updateTask);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(MemberManager::class)->denyRemoveAccess($updateTask);
                $this->container->get(Client::class)->post('modules/whitelist/move',
                    [
                        'json' => [
                            'user' => $updateTask['user'],
                            'module' => $module->getIri(),
                        ],
                    ]
                );
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.success.remove_access_denied', [], 'mis'));
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.update_task.error.remove_access_denied', [], 'mis'));
            }
        }

        return $this->redirectToRoute('mis_third_party_app_update_tasks', ['id' => $module->getIriId()]);
    }
}
