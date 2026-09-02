<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Mis\TroubleTicket\ShowController;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Mis\Module\BusinessUnitPositionDataTableType;
use AppBundle\DataTable\Type\Mis\Module\ModuleDataTableType;
use AppBundle\Form\Type\Mis\Module\ModuleType;
use AppBundle\Form\Type\Mis\Module\ModuleTypeDefaultAssigneeBatchType;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\BusinessUnitPositionType;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\ExtendedType;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\LightType;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\MemberType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/modules', defaults: ['alvest_module' => 'MOD', 'breadcrumb_label' => 'menu.modules.title', 'moduleDomain' => 'mis_modules'])]
class ModuleController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public const RESOURCE_URL = 'modules';
    public const ASSIGNEE_TYPE_URL = 'mis/type_default_assignees';
    public const TRANSLATION_DOMAIN = 'mis';

    public const TYPE_MODULE = 'module';
    public const TYPE_MODULES = 'modules';
    public const TYPE_THIRD_PARTY_APP_EXTENDED = 'extended';
    public const TYPE_THIRD_PARTY_APP_LIGHT = 'light';
    public const TYPE_THIRD_PARTY_APP_CONNECTED = 'connected';

    public const MODULE_TYPE_MAPPING = [
        self::TYPE_MODULE => ModuleType::class,
        self::TYPE_THIRD_PARTY_APP_EXTENDED => ExtendedType::class,
        self::TYPE_THIRD_PARTY_APP_LIGHT => LightType::class,
    ];

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            ViolationMapper::class,
            TranslatorInterface::class,
            FileStreamedResponseFactory::class,
            FormFactoryInterface::class,
        ]);
    }

    #[Route(path: '', name: 'mis_modules_home', methods: 'GET|POST')]
    public function index(Request $request): Response
    {
        $datatable = $this->createDataTable(ModuleDataTableType::class, ModuleDataTableType::RESOURCE);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        if ($datatable->isRequestFromTurboFrame()) {
            // Return only the table's HTML so Turbo can replace the requesting <turbo-frame>
            return $this->createDataTableTurboResponse($datatable);
        }

        return $this->render('mis/modules/list.html.twig', [
            'moduleDatatable' => $datatable->createView(),
        ]);
    }

    #[Route(path: '/{id}/show', name: 'mis_modules_show', methods: 'GET|POST')]
    public function show(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_MODULE, self::TYPE_THIRD_PARTY_APP_EXTENDED, self::TYPE_THIRD_PARTY_APP_LIGHT]])] ApiData $module, Request $request): Response
    {
        return match (mb_strtolower($module['@type'])) {
            self::TYPE_THIRD_PARTY_APP_EXTENDED => $this->getExtended($request, $module),
            self::TYPE_THIRD_PARTY_APP_LIGHT => $this->render('mis/modules/showLight.html.twig', ['module' => $module]),
            default => $this->render('mis/modules/showModule.html.twig', ['module' => $module]),
        };
    }

    #[Route(path: '/{id}/edit', name: 'mis_modules_edit', methods: 'GET|POST')]
    #[Template('mis/modules/edit.html.twig')]
    #[IsGranted('FEATURE_MODULE_WRITE')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_MODULE, self::TYPE_THIRD_PARTY_APP_EXTENDED, self::TYPE_THIRD_PARTY_APP_LIGHT]])] ApiData $module)
    {
        $form = $this->createForm(self::MODULE_TYPE_MAPPING[lcfirst($module['@type'])], $module, ['is_edit' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $module = [...$form->getData(), $form->get('transferOperationalOwner')->getData()];

            unset($module['requiringModules'], $module['misOwner'], $module['typeDefaultAssignees'], $module['whitelistedUsers'], $module['blacklistedUsers']);

            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $module);

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.modules.success.edit', [], self::TRANSLATION_DOMAIN));

                return $this->redirectToRoute('mis_modules_show', ['id' => $module['id']]);
            } catch (ClientException $e) {
                $this->container->get(TranslatorInterface::class)->trans('mis.modules.error.edit', [], self::TRANSLATION_DOMAIN);
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'module' => $module,
        ];
    }

    #[Route(path: '/add', name: 'mis_modules_add', methods: 'GET|POST')]
    #[Template('mis/modules/add.html.twig')]
    #[IsGranted('FEATURE_MODULE_WRITE')]
    public function add(Request $request)
    {
        $form = $this->createForm(ModuleType::class, null, ['is_edit' => false]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.modules.success.add', [], self::TRANSLATION_DOMAIN));

                return $this->redirectToRoute('mis_modules_home');
            } catch (ClientException $e) {
                $this->container->get(TranslatorInterface::class)->trans('mis.modules.error.add', [], self::TRANSLATION_DOMAIN);
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/admin-default-assignees', name: 'mis_modules_admin_default_assignees', requirements: ['id' => '\d+'], defaults: ['label' => 'mis.modules.admin', 'domain' => 'mis'], methods: 'GET|POST')]
    #[IsGranted('FEATURE_TYPE_DEFAULT_ASSIGNEE_BATCH')]
    #[Template('mis/modules/admin.html.twig')]
    public function adminDefaultAssignees(
        #[ApiValueResolverAttribute] HydraCollection $modules,
        #[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL_TYPE])] HydraCollection $types,
        Request $request,
    ) {
        $defaultTypes = [];
        foreach ($types->getSimpleArrayCopy() as $type) {
            $defaultTypes[$type['id']] = ['typeIri' => $type['@id'], 'type' => \sprintf('%s - %s', $type['type'], $type['description'])];
        }

        $modulesIndexed = [];
        foreach ($modules as $module) {
            $moduleId = Iri::id($module);
            $modulesIndexed[$moduleId] = $module->toArray();
            $existingTypeDefaultAssignees = $module['typeDefaultAssignees'];
            $modulesIndexed[$moduleId]['typeDefaultAssignees'] = $defaultTypes;

            foreach ($existingTypeDefaultAssignees as $existingTypeDefaultAssignee) {
                $typeId = Iri::id($existingTypeDefaultAssignee['type']);
                $modulesIndexed[$moduleId]['typeDefaultAssignees'][$typeId]['defaultAssignee'] = $existingTypeDefaultAssignee['defaultAssignee'];
                $modulesIndexed[$moduleId]['typeDefaultAssignees'][$typeId]['@id'] = $existingTypeDefaultAssignee['@id'];
            }
        }

        $form = $this->createForm(ModuleTypeDefaultAssigneeBatchType::class, ['modules' => $modulesIndexed])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $modulesPayload = [];
            $deletions = [];
            foreach ($form->getData()['modules'] as $key => $module) {
                $modulesPayload[$key] = ['@id' => $module['@id']];

                foreach ($module['typeDefaultAssignees'] as $typeDefaultAssignee) {
                    if (isset($typeDefaultAssignee['@id']) && null === $typeDefaultAssignee['defaultAssignee']) {
                        $deletions[] = Iri::id($typeDefaultAssignee['@id']);
                        continue;
                    }

                    if (null === $typeDefaultAssignee['defaultAssignee']) {
                        continue;
                    }

                    $payload = [
                        'defaultAssignee' => $typeDefaultAssignee['defaultAssignee'],
                        'type' => $typeDefaultAssignee['typeIri'],
                    ];

                    if (isset($typeDefaultAssignee['@id'])) {
                        $payload['@id'] = $typeDefaultAssignee['@id'];
                    }

                    $modulesPayload[$key]['typeDefaultAssignees'][] = $payload;
                }

                if (!isset($modulesPayload[$key]['typeDefaultAssignees'])) {
                    $modulesPayload[$key]['typeDefaultAssignees'] = [];
                }
            }

            try {
                foreach ($deletions as $id) {
                    $this->container->get(Client::class)->remove(self::ASSIGNEE_TYPE_URL, $id);
                }

                $this->container->get(Client::class)->save('/mis/type_default_updates', [
                    'modules' => $modulesPayload,
                ]);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.modules.success.admin', [], self::TRANSLATION_DOMAIN));

                return $this->redirectToRoute('mis_modules_admin_default_assignees');
            } catch (ClientException $exception) {
                $this->container->get(ViolationMapper::class)->mapToForm($exception, $form);
                $this->addFlash('error', \sprintf('%s. Reason: %s', $this->container->get(TranslatorInterface::class)->trans('mis.modules.error.admin', [], self::TRANSLATION_DOMAIN), $exception->getMessage()));
            }
        }

        return [
            'modules' => $modulesIndexed,
            'type' => $types,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/convert/third_party_app_light', name: 'mis_modules_convert_third_party_app_light', methods: 'GET')]
    public function convertToThirdPartyAppLight(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_MODULE, self::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module): Response
    {
        $this->container->get(Client::class)->put(\sprintf(self::RESOURCE_URL.'/%s/convert/third_party_app_light', $module->getIriId()), ['json' => []]);

        $this->addFlash('success', 'Third party app converted to light successfully.');

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module->getIriId(),
        ]);
    }

    #[Route(path: '/{id}/convert/third_party_app_extended', name: 'mis_modules_convert_third_party_app_extended', methods: 'GET')]
    public function convertToThirdPartyAppExtended(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => [self::TYPE_THIRD_PARTY_APP_LIGHT]])] ApiData $module): Response
    {
        $this->container->get(Client::class)->put(\sprintf(self::RESOURCE_URL.'/%s/convert/third_party_app_extended', $module->getIriId()), ['json' => []]);

        $this->addFlash('success', 'Third party app converted to extended successfully.');

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module->getIriId(),
        ]);
    }

    protected function getExtended(Request $request, ApiData $module): Response
    {
        $businessUnitPositionsDataTable = $this->createDataTable(BusinessUnitPositionDataTableType::class, \sprintf(BusinessUnitPositionDataTableType::RESOURCE, $module->getIriId()));
        $businessUnitPositionsDataTable->handleRequest($request);
        if ($businessUnitPositionsDataTable->isExporting() && $businessUnitPositionsDataTable->getQuery() instanceof ApiProxyQuery) {
            return $businessUnitPositionsDataTable->getQuery()->export();
        }

        if ($businessUnitPositionsDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($businessUnitPositionsDataTable);
        }

        $businessUnitPositionForm = $this->createForm(BusinessUnitPositionType::class);

        $whitelistForm = $this->container->get(FormFactoryInterface::class)->createNamed('whitelist', MemberType::class);
        $blacklistForm = $this->container->get(FormFactoryInterface::class)->createNamed('blacklist', MemberType::class);

        return $this->render('mis/modules/showExtended.html.twig', [
            'module' => $module,
            'businessUnitPositionForm' => $businessUnitPositionForm->createView(),
            'whitelistForm' => $whitelistForm->createView(),
            'blacklistForm' => $blacklistForm->createView(),
            'businessUnitPositionsDataTable' => $businessUnitPositionsDataTable->createView(),
        ]);
    }
}
