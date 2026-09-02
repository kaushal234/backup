<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\ThirdPartyApp;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Mis\Module\MemberDataTableType;
use AppBundle\DataTable\Type\Mis\Module\UpdateTaskDataTableType;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\BusinessUnitPositionType;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\MemberType;
use AppBundle\Manager\Mis\ThirdPartyApp\MemberManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/modules', defaults: ['alvest_module' => 'MOD', 'breadcrumb_label' => 'menu.modules.title', 'moduleDomain' => 'mis_modules'])]
class ExtendedController extends LightController
{
    public const DELETE_TOKEN = 'delete_business_unit_position';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            FileStreamedResponseFactory::class,
            MemberManager::class,
            FormFactoryInterface::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(path: '/{id}/business_unit_position/add', name: 'mis_third_party_app_business_unit_position_add', methods: 'POST')]
    public function addBusinessUnitPosition(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->createForm(BusinessUnitPositionType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->container->get(Client::class);
                $data = $form->getData();
                $data['thirdPartyApp'] = $module->getIri();
                $client->post('modules/third_party_app/business_unit_positions', [
                    'json' => $data,
                ]);

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.business_unit_position.success.added', [], 'mis'));

                return $this->redirectToRoute('mis_modules_show', [
                    'id' => $module->getIriId(),
                    '_fragment' => 'businessUnitPosition',
                ]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module->getIriId(),
            '_fragment' => 'businessUnitPosition',
        ]);
    }

    #[Route(path: '/{id}/business_unit_position/delete/{businessUnitPositionId}', name: 'mis_third_party_app_business_unit_position_delete', methods: 'GET')]
    public function deleteBusinessUnitPosition(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $businessUnitPositionId): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $businessUnitPosition = $this->container->get(Client::class)->find('modules/third_party_app/business_unit_positions', $businessUnitPositionId);

        if (!$this->isCsrfTokenValid(self::DELETE_TOKEN, $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('mis.business_unit_position.error.remove_form', [], 'mis'));

            return $this->redirectToRoute('mis_modules_show', [
                'id' => $module->getIriId(),
                '_fragment' => 'businessUnitPosition',
            ]);
        }

        try {
            $this->container->get(Client::class)->remove('modules/third_party_app/business_unit_positions', $businessUnitPosition->getIriId());
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.business_unit_position.success.remove', [], 'mis'));
        } catch (ClientException $e) {
            $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('mis.business_unit_position.error.remove', [], 'mis'), $e->getMessage()));
        }

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module->getIriId(),
            '_fragment' => 'businessUnitPosition',
        ]);
    }

    #[Route(path: '/{id}/admins/add', name: 'mis_third_party_app_admin_add', methods: 'GET|POST')]
    public function addAdministrator(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->createForm(MemberType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->container->get(MemberManager::class)->forceAdd($module, $data['user'], true);

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.member.success.admin_added', [], 'mis'));

                return $this->redirectToRoute('mis_third_party_app_members', ['id' => $module->getIriId()]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('mis.member.error.admin_added', [], 'mis'), $e->getMessage()));
            }
        }

        return $this->render('mis/modules/third_party_app/members/add_admin.html.twig', [
            'module' => $module,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/whitelist/add', name: 'mis_third_party_app_whitelist_add', methods: 'POST')]
    public function addUserToWhitelist(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->container->get(FormFactoryInterface::class)->createNamed('whitelist', MemberType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->container->get(Client::class);
                $data = $form->getData();
                $client->post('modules/whitelist/add',
                    [
                        'json' => [
                            'user' => $data['user'],
                            'module' => $module->getIri(),
                        ],
                    ]
                );

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.whitelist.success.added', [], 'mis'));

                return $this->redirectToRoute('mis_modules_show', [
                    'id' => $module->getIriId(),
                    '_fragment' => 'whitelist',
                ]);
            } catch (ClientException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module->getIriId(),
            '_fragment' => 'whitelist',
        ]);
    }

    #[Route(path: '/{id}/whitelist/{userId}/remove', name: 'mis_third_party_app_whitelist_remove', methods: 'GET')]
    public function removeUserFromWhitelist(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $userId): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $user = $this->container->get(Client::class)->find('people', $userId);
        if (!$this->isCsrfTokenValid('delete_user', $request->query->get('_token'))) {
            $this->addFlash('error', \sprintf('%s: please refresh your form.', $this->container->get(TranslatorInterface::class)->trans('mis.whitelist.error.removed', [], 'mis')));

            return $this->redirectToRoute('mis_modules_show', [
                'id' => $module->getIriId(),
                '_fragment' => 'whitelist',
            ]);
        }

        try {
            $this->container->get(Client::class)->post('modules/whitelist/remove',
                [
                    'json' => [
                        'user' => $user->getIri(),
                        'module' => $module->getIri(),
                    ],
                ]
            );
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.whitelist.success.removed', [], 'mis'));
        } catch (ClientException $e) {
            $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('mis.whitelist.error.removed', [], 'mis'), $e->getMessage()));
        }

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module->getIriId(),
            '_fragment' => 'whitelist',
        ]);
    }

    #[Route(path: '/{id}/blacklist/add', name: 'mis_third_party_app_blacklist_add', methods: 'POST')]
    public function addUserToBlacklist(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->container->get(FormFactoryInterface::class)->createNamed('blacklist', MemberType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->container->get(Client::class);
                $data = $form->getData();
                $client->post('modules/blacklist/add',
                    [
                        'json' => [
                            'user' => $data['user'],
                            'module' => $module->getIri(),
                        ],
                    ]
                );

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.blacklist.success.added', [], 'mis'));

                return $this->redirectToRoute('mis_modules_show', [
                    'id' => $module->getIriId(),
                    '_fragment' => 'blacklist',
                ]);
            } catch (ClientException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module->getIriId(),
            '_fragment' => 'blacklist',
        ]);
    }

    #[Route(path: '/{id}/blacklist/{userId}/remove', name: 'mis_third_party_app_blacklist_remove', methods: 'GET')]
    public function removeUserFromBlacklist(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $userId): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $user = $this->container->get(Client::class)->find('people', $userId);
        if (!$this->isCsrfTokenValid('delete_user', $request->query->get('_token'))) {
            $this->addFlash('error', \sprintf('%s: please refresh your form.', $this->container->get(TranslatorInterface::class)->trans('mis.blacklist.error.removed', [], 'mis')));

            return $this->redirectToRoute('mis_modules_show', [
                'id' => $module->getIriId(),
                '_fragment' => 'blacklist',
            ]);
        }

        try {
            $this->container->get(Client::class)->post('modules/blacklist/remove',
                [
                    'json' => [
                        'user' => $user->getIri(),
                        'module' => $module->getIri(),
                    ],
                ]
            );
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.blacklist.success.removed', [], 'mis'));
        } catch (ClientException $e) {
            $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('mis.blacklist.error.removed', [], 'mis'), $e->getMessage()));
        }

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module->getIriId(),
            '_fragment' => 'blacklist',
        ]);
    }

    #[Route(path: '/{id}/members/{userId}/delete', name: 'mis_third_party_app_user_delete', methods: 'GET')]
    public function deleteUser(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $userId): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $member = $this->container->get(Client::class)->find('modules/third_party_app/members', $userId);
        if (!$this->isCsrfTokenValid('delete_user', $request->query->get('_token'))) {
            $this->addFlash('error', \sprintf('%s: please refresh your form.', $this->container->get(TranslatorInterface::class)->trans('mis.member.error.remove_user', [], 'mis')));

            return $this->redirectToRoute('mis_modules_show', ['id' => $module->getIriId()]);
        }

        try {
            $this->container->get(Client::class)->remove('modules/third_party_app/members', $member->getIriId());
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.member.success.removed', [], 'mis'));
        } catch (ClientException $e) {
            $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('mis.member.error.remove_user', [], 'mis'), $e->getMessage()));
        }

        return $this->redirectToRoute('mis_modules_show', ['id' => $module->getIriId()]);
    }

    #[Route(path: '/{id}/members', name: 'mis_third_party_app_members', methods: ['GET', 'POST'])]
    public function members(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $id): Response
    {
        $datatable = $this->createDataTable(MemberDataTableType::class, \sprintf(MemberDataTableType::RESOURCE, $id));
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return $this->render('mis/modules/third_party_app/members/list.html.twig', [
            'members' => $datatable->createView(),
            'module' => $module,
        ]);
    }

    #[Route(path: '{id}/members/{memberId}/remove_confirmation_modal', name: 'mis_third_party_app_members_remove_confirm', methods: 'GET')]
    public function removeConfirmation(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $memberId): Response
    {
        $member = $this->container->get(Client::class)->find('modules/third_party_app/members', $memberId);

        return $this->render('mis/modules/third_party_app/members/modal/remove_confirmation.html.twig', [
            'module' => $module,
            'member' => $member,
        ]);
    }

    #[Route(path: '/{id}/members/{memberId}/remove', name: 'mis_third_party_app_members_remove', methods: 'GET')]
    public function removeMember(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $memberId): RedirectResponse
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        if (!$this->isCsrfTokenValid('remove_member', $request->query->get('_token'))) {
            $this->addFlash('error', \sprintf('%s: please refresh your form.', $this->container->get(TranslatorInterface::class)->trans('mis.member.error.remove_user', [], 'mis')));

            return $this->redirectToRoute('mis_third_party_app_members', ['id' => $module->getIriId()]);
        }

        try {
            $this->container->get(MemberManager::class)->remove($memberId);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.member.success.new_task', [], 'mis'));
        } catch (ClientException $e) {
            $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('mis.member.error.remove_user', [], 'mis'), $e->getMessage()));
        }

        return $this->redirectToRoute('mis_third_party_app_members', ['id' => $module->getIriId()]);
    }

    #[Route(path: '/{id}/members/{memberId}/remove_admin', name: 'mis_third_party_app_members_remove_admin', methods: 'GET')]
    public function removeAdministrator(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $memberId): RedirectResponse
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        if (!$this->isCsrfTokenValid('remove_member', $request->query->get('_token'))) {
            $this->addFlash('error', \sprintf('%s: please refresh your form.', $this->container->get(TranslatorInterface::class)->trans('mis.member.error.remove_user', [], 'mis')));

            return $this->redirectToRoute('mis_third_party_app_members', ['id' => $module->getIriId()]);
        }

        try {
            $this->container->get(MemberManager::class)->forceRemove($memberId);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.member.success.admin_removed', [], 'mis'));
        } catch (ClientException $e) {
            $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('mis.member.error.remove_user', [], 'mis'), $e->getMessage()));
        }

        return $this->redirectToRoute('mis_third_party_app_members', ['id' => $module->getIriId()]);
    }

    #[Route(path: '/{id}/update_tasks', name: 'mis_third_party_app_update_tasks', methods: ['GET', 'POST'])]
    public function updateTasks(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module): Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $datatable = $this->createDataTable(UpdateTaskDataTableType::class, \sprintf(UpdateTaskDataTableType::RESOURCE, $module->getIriId()));
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return $this->render('mis/modules/third_party_app/update_tasks/list.html.twig', [
            'module' => $module,
            'datatable' => $datatable->createView(),
        ]);
    }

    #[Route(path: '/{id}/members/{memberId}/promote', name: 'mis_third_party_app_members_promote', methods: 'GET')]
    public function promoteMember(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $memberId): RedirectResponse
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $this->container->get(MemberManager::class)->promote($memberId);

        return $this->redirectToRoute('mis_third_party_app_members', ['id' => $module->getIriId()]);
    }

    #[Route(path: '/{id}/members/{memberId}/demote', name: 'mis_third_party_app_members_demote', methods: 'GET')]
    public function demoteMember(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $memberId): RedirectResponse
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $this->container->get(MemberManager::class)->demote($memberId);

        return $this->redirectToRoute('mis_third_party_app_members', ['id' => $module->getIriId()]);
    }
}
