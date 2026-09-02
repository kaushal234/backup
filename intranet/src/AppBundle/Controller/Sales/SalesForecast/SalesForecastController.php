<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\SalesForecast;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use ApiBundle\Request\RequestParameterBagFilter;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Sales\ForecastClosureController;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Sales\SalesForecastDataTableType;
use AppBundle\Filters\Type\Sales\SalesForecastFilterType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\LegacyIdSearchType;
use AppBundle\Form\Type\Sales\CompetitorPricing\CompetitorPricingType;
use AppBundle\Form\Type\Sales\ForecastClosure\ForecastClosureAddCollectionType;
use AppBundle\Form\Type\Sales\ForecastClosure\ForecastClosureChoiceType;
use AppBundle\Form\Type\Sales\SalesForecast\SalesForecastEditType;
use AppBundle\Form\Type\Sales\SalesForecast\SalesForecastEstimatedSaleDateType;
use AppBundle\Form\Type\Sales\SalesForecast\SalesForecastExportType;
use AppBundle\Form\Type\Sales\SalesForecast\SalesForecastLinkType;
use AppBundle\Form\Type\Sales\SalesForecast\SalesForecastsCloseType;
use AppBundle\Form\Type\Sales\SalesForecast\SalesForecastTransferType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/sales-forecasts', defaults: ['alvest_module' => 'SFR', 'moduleDomain' => 'sales_forecasts'])]
class SalesForecastController extends AbstractController
{
    final public const RESOURCE_URL = 'sales/sales_forecasts';

    public function __construct(
        private readonly Client $client,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TranslatorInterface $translator,
        private readonly FormFactoryInterface $formFactory,
        private readonly ViolationMapper $violationMapper,
        private readonly CsvStreamedResponseFactory $csvStreamedResponseFactory,
        private readonly FileStreamedResponseFactory $fileStreamedResponseFactory,
        private readonly FileManager $fileManager,
        private readonly RequestParameterBagFilter $requestParameterBagFilter,
    ) {
    }

    #[Route(path: '/home', name: 'sales_forecasts_home')]
    public function home(): RedirectResponse
    {
        if (
            $this->authorizationChecker->isGranted('ACL_ROLE_ASM')
            && !$this->authorizationChecker->isGranted('ACL_ROLE_EVP')
        ) {
            return $this->redirectToRoute('sales_forecasts_dashboard_asm');
        }

        return $this->redirectToRoute('sales_forecasts_dashboard');
    }

    #[Route(path: '', name: 'sales_forecasts_list', methods: ['GET', 'POST'])]
    #[Route(path: '/gantt', name: 'sales_forecasts_list_gantt', methods: ['GET', 'POST'])]
    #[Route(path: '/quick-edit', name: 'sales_forecasts_list_quick_edit', methods: ['GET', 'POST'])]
    public function list(Request $request, DataTableFactoryInterface $dataTableFactory)
    {
        foreach ($request->query->all() as $queryParam => $value) {
            if ('ALL' === $value) {
                $request->query->remove($queryParam);
            }
        }

        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $legacyIdSearchForm = $this->createForm(LegacyIdSearchType::class, null, [
            'legacy_id_label' => false,
            'legacy_id_placeholder' => 'By Legacy ID',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $this->client->get(\sprintf('sales/sales_forecasts/%s', $id));

                return $this->redirectToRoute('sales_forecasts_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', Response::HTTP_NOT_FOUND === $e->getCode() ? \sprintf('SFR #%s does not exist', $id) : $this->translator->trans('sales_forecasts.messages.errors.not_allowed', [], 'sales_forecasts'));
            }
        }

        $legacyIdSearchForm->handleRequest($request);
        if ($legacyIdSearchForm->isSubmitted() && $legacyIdSearchForm->isValid()) {
            $legacyId = $legacyIdSearchForm->get('legacyId')->getData();
            try {
                $customer = $this->client->findOneBy(self::RESOURCE_URL, ['legacyId' => $legacyId]);

                return $this->redirectToRoute('sales_forecasts_show', ['id' => Iri::id($customer)]);
            } catch (\RangeException $e) {
                $this->addFlash('error', Response::HTTP_NOT_FOUND === $e->getCode() ? \sprintf('SFR #%s does not exist', $legacyId) : $this->translator->trans('sales_forecasts.messages.errors.not_allowed', [], 'sales_forecasts'));
            }
        }

        $parameters = ['open' => 'true'];

        if (!(bool) $request->query->get('open')) {
            $parameters = [];
            $request->query->remove('open');
        }

        $route = $request->attributes->get('_route');

        $formFilter = null;
        if ($search = $request->query->has('search')) {
            $formFilter = $this
                ->formFactory
                ->createNamed(
                    '',
                    SalesForecastFilterType::class, [],
                    [
                        'action' => $this->generateUrl($route),
                        'method' => 'GET',
                    ]
                );

            $formFilter->handleRequest($request);
            if ($formFilter->isSubmitted() && $formFilter->isValid()) {
                $parameters = array_merge($parameters, $formFilter->getData());
            }
        } else {
            $parameters = array_merge($parameters, $request->query->all());
            unset($parameters['gantt']);
        }

        if (isset($parameters['hot_deals']) && !$parameters['hot_deals']) {
            unset($parameters['hot_deals']);
        }

        foreach ($parameters as $key => $value) {
            if (null === $value || '' === $value) {
                if (!$search) {
                    unset($parameters[$key]);
                }
                continue;
            }
            if (false !== mb_strpos($key, '-')) {
                $parameters[preg_replace('/(.*)-(.*)/', '$1[$2]', $key)] = $value;
                unset($parameters[$key]);
            }
        }

        if (isset($parameters['delinquent']) && !$parameters['delinquent']) {
            unset($parameters['delinquent']);
        }

        if (isset($parameters['closedAt[after]'])
            || isset($parameters['hot_deals'])
            || (isset($parameters['status']) && \in_array($parameters['status'], ['LOST', 'ORDERED', 'CANCELLED', 'PARTIAL'], true))
        ) {
            unset($parameters['open']);
        }

        if (isset($parameters['family'])) {
            $parameters['product.family'] = $parameters['family'];
            unset($parameters['family']);
        }

        if ('sales_forecasts_list_quick_edit' === $route) {
            $parameters['status'] = ['DELAYED', 'IN_PROGRESS', 'BUDGET'];
            unset($parameters['open']);
            $parameters['itemsPerPage'] = 100;

            $salesForecasts = $this->client->findBy(self::RESOURCE_URL, $parameters, ['masterSalesForecast.id' => 'desc'], ['raw_results' => true]);
            $sfrs = [];
            foreach ($salesForecasts['hydra:member'] as $sfr) {
                $sfrs[$sfr['@id']] = $sfr;
            }

            return $this->render('sales/sales_forecasts/list_quick_edit.html.twig', [
                'formFilter' => $search && $formFilter ? $formFilter->createView() : null,
                'filters' => $parameters,
                'initialState' => [
                    'sfr' => ['salesForecasts' => $sfrs],
                ],
                'idSearchForm' => $idSearchForm->createView(),
                'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
            ]);
        }

        $salesForecasts = [];

        if ('sales_forecasts_list_gantt' === $route) {
            $parameters = $this->requestParameterBagFilter->filter($parameters, $this->client->findby(self::RESOURCE_URL, ['itemsPerPage' => 0]));
            if (!$search || ([] !== $parameters)) {
                $salesForecasts = $this->client->findBy(self::RESOURCE_URL, $parameters, ['estimatedSaleDate' => 'desc', 'successPercentage' => 'desc']);
            }

            return $this->render('sales/sales_forecasts/list_gantt.html.twig', [
                'formFilter' => $search && $formFilter ? $formFilter->createView() : null,
                'filters' => $parameters,
                'salesForecasts' => $salesForecasts,
                'datePeriod' => new \DatePeriod(new \DateTime('first day of this month'), new \DateInterval('P1M'), new \DateTime('first day of +6 months')),
                'idSearchForm' => $idSearchForm->createView(),
                'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
            ]);
        }

        $salesForecastsDataTable = $dataTableFactory->create(SalesForecastDataTableType::class, SalesForecastDataTableType::RESOURCE);
        $salesForecastsDataTable->handleRequest($request);
        if ($salesForecastsDataTable->isExporting() && $salesForecastsDataTable->getQuery() instanceof ApiProxyQuery) {
            return $salesForecastsDataTable->getQuery()->export();
        }

        return $this->render('sales/sales_forecasts/list_basic.html.twig', [
            'salesForecastsDataTable' => $salesForecastsDataTable->createView(),
        ]);
    }

    #[Route(path: '/my-area', name: 'sales_forecasts_my_area', methods: ['GET', 'POST'])]
    #[Template('sales/sales_forecasts/list_basic.html.twig')]
    public function listMyArea(Request $request, DataTableFactoryInterface $dataTableFactory)
    {
        $salesForecastsDataTable = $dataTableFactory->create(SalesForecastDataTableType::class, SalesForecastDataTableType::RESOURCE);
        $salesForecastsDataTable->handleRequest($request);
        if ($salesForecastsDataTable->isExporting() && $salesForecastsDataTable->getQuery() instanceof ApiProxyQuery) {
            return $salesForecastsDataTable->getQuery()->export();
        }

        $salesForecastsDataTable
            ->setFiltrationData(FiltrationData::fromArray(['open' => 'true']))
            ->setSortingData(SortingData::fromArray(['estimatedSaleDate' => 'desc', 'successPercentage' => 'desc']))
        ;

        return [
            'salesForecastsDataTable' => $salesForecastsDataTable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'sales_forecasts_show')]
    #[Template('sales/sales_forecasts/show.html.twig')]
    public function show($id, Request $request)
    {
        try {
            $salesForecast = $this->client->find(self::RESOURCE_URL, $id);
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('sales_forecasts.messages.errors.not_allowed', [], 'sales_forecasts'));

            return $this->redirectToRoute('sales_forecasts_home');
        }

        $form = $this->formFactory->createNamed('salesForecast', SimpleFileType::class, null, [
            'action' => $this->generateUrl('sales_forecasts_show', ['id' => $id, 'tab' => 'files']),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile($salesForecast, $file, self::RESOURCE_URL, $form->get('description')->getData(), 'files');
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('customers.messages.success.file', [], 'sales_customers')
                );

                return $this->redirectToRoute('sales_forecasts_show', ['id' => $salesForecast->getIriId(), 'tab' => 'files']);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            } catch (ServerException $e) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('errors.something_went_wrong', [], 'messages')
                );
            }
        }

        $linkedSalesForecasts = $this->client->findBy(
            self::RESOURCE_URL,
            [
                'masterSalesForecast' => $salesForecast['masterSalesForecast']['@id'],
            ]
        );

        $masterSalesForecastIds = [];

        $linkedSalesForecasts = array_reduce(
            $linkedSalesForecasts->getIterator()->getArrayCopy(),
            static function ($memo, $linkedSalesForecast) use ($salesForecast, &$masterSalesForecastIds) {
                $masterSalesForecastIds[] = $linkedSalesForecast['id'];

                if ($linkedSalesForecast['id'] !== $salesForecast['id']) {
                    $memo[] = $linkedSalesForecast;
                }

                return $memo;
            },
            []
        );

        $estimatedSaleDateForm = $this
            ->formFactory
            ->createNamed(
                'comment',
                SalesForecastEstimatedSaleDateType::class,
                ['estimatedSaleDate' => $salesForecast['estimatedSaleDate']],
                [
                    'showSynchronized' => \count($linkedSalesForecasts) > 0,
                ]
            );

        $estimatedSaleDateForm->handleRequest($request);
        if ($estimatedSaleDateForm->isSubmitted() && $estimatedSaleDateForm->isValid()) {
            $data = $estimatedSaleDateForm->getData();
            $data['@id'] = $salesForecast['@id'];
            unset($data['masterSalesForecast'], $data['sso']);
            try {
                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('sales_forecasts.messages.success.edit', [], 'sales_forecasts')
                );

                return $this->redirectToRoute('sales_forecasts_show', ['id' => $salesForecast->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $estimatedSaleDateForm);
                $this->addFlash('error', $this->translator->trans('sales_forecasts.messages.errors.edit_fail', [], 'sales_forecasts'));
            }
        }

        $linkForm = $this
            ->formFactory
            ->createNamed(
                'linked',
                SalesForecastLinkType::class,
                [],
                [
                    'masterSalesForecastExcluded' => $masterSalesForecastIds,
                    'salesForecast' => $salesForecast,
                ]
            );

        $linkForm->handleRequest($request);
        if ($linkForm->isSubmitted() && $linkForm->isValid()) {
            $data = $linkForm->getData();
            foreach ($data['salesForecasts'] as $linkSfr) {
                try {
                    $this->client->post(\sprintf('%s/link', $linkSfr), [
                        'json' => [
                            'salesForecastToLink' => $salesForecast->getIri(),
                        ],
                    ]);
                } catch (ClientException $e) {
                    $this->addFlash('error', $this->translator->trans('sales_forecasts.messages.errors.link_fail', [], 'sales_forecasts'));
                }
            }
            $this->addFlash('success', $this->translator->trans('sales_forecasts.messages.success.link', [], 'sales_forecasts'));

            return $this->redirectToRoute('sales_forecasts_show', ['id' => $salesForecast->getIriId()]);
        }

        $forecastClosures = array_map(static function (array $forecastClosure) use ($salesForecast) {
            $forecastClosure['salesForecast'] = $salesForecast;

            return $forecastClosure;
        }, $salesForecast['forecastClosures']);

        $competitorPricings = [];
        foreach ($forecastClosures as $forecastClosure) {
            foreach ($forecastClosure['competitorPricings'] as $competitorPricing) {
                $competitorPricing['forecastClosure'] = $forecastClosure;
                $competitorPricings[] = $competitorPricing;
            }
        }

        $leadTime = $this->client->findBy('lead_times', ['productFamily' => Iri::id($salesForecast['product']['family']['@id']), 'factory' => Iri::id($salesForecast['factory']['@id'])]);

        return [
            'currency' => null !== $salesForecast['sso']['currency'] ? $salesForecast['sso']['currency']['name'] : 'USD',
            'form' => $form->createView(),
            'salesForecast' => $salesForecast->toArray(),
            'forecastClosures' => $forecastClosures,
            'competitorPricings' => $competitorPricings,
            'linkedSalesForecasts' => $linkedSalesForecasts,
            'linkForm' => $linkForm->createView(),
            'estimatedSaleDateForm' => $estimatedSaleDateForm->createView(),
            'leadTime' => $leadTime->first(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'sales_forecasts_edit', methods: ['GET|POST'])]
    #[Template('sales/sales_forecasts/edit.html.twig')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $salesForecast)
    {
        $iri = $salesForecast['@id'];

        if (!$this->authorizationChecker->isGranted('SALES_FORECAST_EDIT_VOTER', $iri) && !$this->authorizationChecker->isGranted('MOO_SFR')) {
            $this->addFlash('error', $this->translator->trans('sales_forecasts.messages.errors.not_allowed', [], 'sales_forecasts'));

            return $this->redirectToRoute('sales_forecasts_home');
        }

        $linkedSalesForecasts = $this->client->findBy(self::RESOURCE_URL, ['masterSalesForecast' => $salesForecast['masterSalesForecast']['@id']]);

        $linkedSalesForecasts = array_reduce(
            $linkedSalesForecasts->getIterator()->getArrayCopy(), static function ($memo, $linkedSalesForecasts) use ($salesForecast) {
                if ($linkedSalesForecasts['id'] !== $salesForecast['id']) {
                    $memo[] = $linkedSalesForecasts;
                }

                return $memo;
            },
            []
        );

        $authorizedFields = $this->client->get('/fields', ['query' => ['iri' => $salesForecast['@id'], 'method' => 'PUT']]);
        $form = $this
            ->formFactory
            ->createNamed(
                'sales_forecasts_form',
                SalesForecastEditType::class,
                $salesForecast,
                [
                    'authorizedFields' => $authorizedFields,
                    'countLinkedSFR' => \count($linkedSalesForecasts),
                ]
            );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = [
                    ...$form->getData(),
                    'synchronized' => $form->has('synchronized') ? $form->get('synchronized')->getData() : false,
                    'comment' => $form->get('comment')->getData(),
                    'notificationRestricted' => $form->get('notificationRestricted')->getData(),
                    'notifyPackage' => $form->get('notifyPackage')->getData(),
                ];

                unset($data['masterSalesForecast']);

                $results = array_intersect_key($data, array_flip($authorizedFields));
                $results['@id'] = $data['@id'];

                $this->client->save(self::RESOURCE_URL, $results);

                $this->addFlash(
                    'success',
                    $this->translator->trans('sales_forecasts.messages.success.edit', [], 'sales_forecasts')
                );

                return $this->redirectToRoute('sales_forecasts_show', ['id' => Iri::id($salesForecast)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
                $this->addFlash(
                    'error',
                    $this->translator->trans('sales_forecasts.messages.errors.edit_fail', [], 'sales_forecasts')
                );
            }
        }

        return [
            'linkedSalesForecasts' => $linkedSalesForecasts,
            'salesForecast' => $salesForecast,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'sales_forecasts_delete', methods: ['GET'])]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $salesForecast): RedirectResponse
    {
        if (!$this->authorizationChecker->isGranted('FEATURE_SALES_FORECAST_ADMIN_EDIT', $salesForecast['@id']) && !$this->authorizationChecker->isGranted('MOO_SFR')) {
            $this->addFlash('error', $this->translator->trans('sales_forecasts.messages.errors.not_allowed_delete', [], 'sales_forecasts'));

            return $this->redirectToRoute('sales_forecasts_show', ['id' => $salesForecast->getIriId()]);
        }

        try {
            $this->client->remove(self::RESOURCE_URL, $salesForecast->getIriId());
            $this->addFlash('success', $this->translator->trans('sales_forecasts.messages.success.delete', [], 'sales_forecasts'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('sales_forecasts.messages.errors.delete_fail', [], 'sales_forecasts'));
        }

        return $this->redirectToRoute('sales_forecasts_home');
    }

    #[Route(path: '/{id}/unlink', name: 'sales_forecasts_unlink')]
    public function unlink(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $salesForecast, Request $request): RedirectResponse
    {
        try {
            $this->client->put(\sprintf('sales/sales_forecasts/%s/unlink', $salesForecast->getIriId()));
            $this->addFlash('success', $this->translator->trans('sales_forecasts.messages.success.unlink', [], 'sales_forecasts'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('sales_forecasts.messages.errors.unlink_fail', [], 'sales_forecasts'));
        }

        $target = $request->headers->get('referer');
        if (null === $target) {
            $target = $this->generateUrl('sales_forecasts_home');
        }

        return $this->redirect($target);
    }

    #[Route(path: '/add', name: 'sales_forecasts_add', methods: ['GET', 'POST'])]
    #[Template('sales/sales_forecasts/add.html.twig')]
    #[IsGranted('FEATURE_SALES_FORECAST_CREATE')]
    public function add()
    {
        $asms = $this->client->findBy('people',
            ['hidden' => 0, 'disabled' => 0, 'pagination' => 0, 'normalization_groups_override' => ['people_list'], 'acls.group.name' => ['ROLE_ASM', 'GG_SALES_AGENTS']],
            ['lastname' => 'asc', 'firstname' => 'asc'],
            ['raw_results' => true]
        );

        $ssos = $this->client->findBy('locations', ['capability.sso' => 1], ['name' => 'asc'], ['raw_results' => true]);
        $factories = $this->client->findBy('locations', ['capability.factory' => 1], ['name' => 'asc'], ['raw_results' => true]);
        $emissionRatings = $this->client->findBy('emission_ratings', [], ['name' => 'asc'], ['raw_results' => true]);

        $connectedUser = $this->client->get('me');

        $initialState = [
            'emissionRating' => [
                'emissionRatings' => $emissionRatings['hydra:member'],
            ],
            'user' => [
                'asms' => $asms['hydra:member'],
                'details' => $connectedUser,
            ],
            'location' => [
                'ssos' => $ssos['hydra:member'],
                'factories' => $factories['hydra:member'],
            ],
        ];

        return [
            'initialState' => $initialState,
        ];
    }

    #[Route(path: '/{id}/close', name: 'sales_forecasts_close', methods: ['GET|POST'])]
    #[Template('sales/sales_forecasts/close_sfr.html.twig')]
    public function close(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $salesForecast)
    {
        $closeForm = $this
            ->formFactory
            ->createNamed(
                '',
                SalesForecastsCloseType::class
            );

        $closeForm->handleRequest($request);
        if ($closeForm->isSubmitted() && $closeForm->isValid()) {
            $data = $closeForm->getData();
            $status = $data['status'];
            if (isset($data['comment']) && null !== $data['comment'] && 'CANCELLED' === $status) {
                $data['@id'] = $salesForecast['@id'];
                try {
                    $this->client->put(\sprintf('sales/sales_forecasts/%d/status', Iri::id($salesForecast)), ['json' => $data]);

                    return $this->redirectToRoute('sales_forecasts_show', ['id' => Iri::id($salesForecast)]);
                } catch (ClientException $e) {
                    $this->violationMapper->mapToForm($e, $closeForm);
                }
            } elseif ('CANCELLED' !== $status) {
                return $this->redirectToRoute('sales_forecasts_open_fcr', ['id' => $salesForecast['id'], 'status' => $status]);
            }
        }

        return [
            'cancelled' => true,
            'salesForecast' => $salesForecast,
            'closeForm' => $closeForm->createView(),
        ];
    }

    #[Route(path: '/{id}/open-fcr/{status}', name: 'sales_forecasts_open_fcr', methods: ['GET|POST'])]
    #[Template('sales/sales_forecasts/close_sfr.html.twig')]
    public function closeAndAddCPR(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $salesForecast, $status)
    {
        $statusChoices = [];
        $errors = [];
        switch ($status) {
            case 'LOST':
            case 'ORDERED':
                $statusChoices = [$status];
                break;
            case 'PARTIAL':
                $fcrAlreadyCreated = $this->client->findBy('sales/forecast_closures', ['salesForecast' => $salesForecast['@id']]);
                $fcrStatus = array_reduce($fcrAlreadyCreated->getIterator()->getArrayCopy(), static function ($memo, $fcrAlreadyCreated) {
                    $memo[] = $fcrAlreadyCreated['status'];

                    return $memo;
                }, []);

                $partialChoices = ['PARTIAL-LOST', 'PARTIAL-ORDERED'];

                $statusChoices = array_diff($partialChoices, $fcrStatus);
                break;
        }

        $closeForm = $this
            ->formFactory
            ->createNamed(
                '',
                ForecastClosureAddCollectionType::class,
                ['forecastClosures' => array_fill(0, \count($statusChoices), [])],
                [
                    'status' => $statusChoices,
                    'sales_forecast' => $salesForecast,
                ]
            );

        $closeForm->handleRequest($request);
        if ($closeForm->isSubmitted() && $closeForm->isValid()) {
            $data = $closeForm->getData();

            if (\count($data['forecastClosures']) > 1 && array_values($statusChoices) !== array_column($data['forecastClosures'], 'status')) {
                if (array_values($statusChoices) !== array_column($data['forecastClosures'], 'status')) {
                    $this->addFlash(
                        'error',
                        $this->translator->trans('forecast_closures.errors.same_status', [], 'forecast_closures')
                    );

                    return $this->redirectToRoute('sales_forecasts_open_fcr', ['id' => $salesForecast['id'], 'status' => $status]);
                }
            }

            foreach ($data['forecastClosures'] as $index => $forecastClosure) {
                $forecastClosure['salesForecast'] = $salesForecast['@id'];
                /** @var FormInterface $formFile */
                $formFile = $forecastClosure['file'];
                unset($forecastClosure['file']);
                try {
                    /** @var UploadedFile $file */
                    $file = $formFile['file'];

                    $fcrSaved = $this->client->save('sales/forecast_closures', $forecastClosure);

                    if ($file instanceof UploadedFile) {
                        $this->fileManager->uploadFile($fcrSaved, $file, ForecastClosureController::RESOURCE_URL, $formFile['description'], 'files');
                    }
                    unset($data['forecastClosures'][$index]);
                } catch (ClientException $e) {
                    $errors[$index] = $e;
                }
            }

            if ([] !== $errors) {
                $data['forecastClosures'] = array_values($data['forecastClosures']);
                $errors = array_values($errors);
                $remainingStatuses = array_column($data['forecastClosures'], 'status');
                $closeForm = $this
                    ->formFactory
                    ->createNamed(
                        '',
                        ForecastClosureAddCollectionType::class,
                        ['forecastClosures' => $data['forecastClosures']],
                        [
                            'status' => $remainingStatuses,
                            'sales_forecast' => $salesForecast,
                        ]
                    );

                foreach ($errors as $index => $e) {
                    /** @var ClientException $e */
                    $content = json_decode((string) $e->getResponse()->getContent(false), true);

                    $this->violationMapper->contentMapToFormCollection(
                        $content,
                        $closeForm->get('forecastClosures'),
                        $index
                    );
                }

                $this->addFlash(
                    'error',
                    $this->translator->trans('forecast_closures.errors.create_fail', [], 'forecast_closures')
                );
            } else {
                $this->addFlash(
                    'success',
                    $this->translator->trans('forecast_closures.messages.success_create', [], 'forecast_closures')
                );

                return $this->redirectToRoute('sales_forecasts_add_cpr', ['id' => Iri::id($salesForecast)]);
            }
        }

        return [
            'status' => $status,
            'closeForm' => $closeForm->createView(),
            'cancelled' => false,
            'salesForecast' => $salesForecast,
        ];
    }

    #[Route(path: '/{id}/add-cpr', name: 'sales_forecasts_add_cpr', methods: ['GET|POST'])]
    #[Template('sales/forecast_closures/add_cpr.html.twig')]
    public function addCPR(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $salesForecast, Request $request)
    {
        if ($request->query->has('finish')) {
            $this->client->put(\sprintf('sales/sales_forecasts/%d/notify', Iri::id($salesForecast)));

            return $this->redirectToRoute('sales_forecasts_show', ['id' => $salesForecast->getIriId()]);
        }

        $fcrForm = $this->formFactory->createNamed('forecast_closure', ForecastClosureChoiceType::class, null, [
            'method' => 'GET',
            'salesForecast' => $salesForecast['@id'],
            'csrf_protection' => false,
        ]);

        if (null !== ($fcrId = $request->query->get('forecast_closure'))) {
            $forecastClosure = $this->client->find('sales/forecast_closures', $fcrId);
            $competitorPricingForm = $this
                ->formFactory
                ->createNamed(
                    '',
                    CompetitorPricingType::class,
                    [],
                    [
                        'submit_label' => 'forecast_closures.form.submit_and_new',
                        'submit_translation_domain' => 'forecast_closures',
                    ]
                );

            $competitorPricingForm->add('submit_and_back', SubmitType::class, [
                'translation_domain' => 'forecast_closures',
                'label' => 'forecast_closures.form.submit_and_back',
                'attr' => ['class' => 'btn btn-warning'],
            ]);

            $competitorPricingForm->handleRequest($request);
            if ($competitorPricingForm->isSubmitted() && $competitorPricingForm->isValid()) {
                try {
                    $data = $competitorPricingForm->getData();

                    /** @var ClickableInterface $submitFinal */
                    $submitFinal = $competitorPricingForm->get('submit_and_back');

                    $formFile = $data['file'];
                    unset($data['file']);

                    $data['forecastClosure'] = $forecastClosure['@id'];
                    $data['last'] = $submitFinal->isClicked();

                    $cprSaved = $this->client->save('sales/competitor_pricings', $data);

                    /** @var UploadedFile $file */
                    $file = $formFile['file'];
                    if ($file instanceof UploadedFile) {
                        $this->fileManager->uploadFile($cprSaved, $file, 'sales/competitor_pricings', $formFile['description'], 'files');
                    }

                    $this->addFlash(
                        'success',
                        $this->translator->trans('forecast_closures.messages.success_new_cpr', [], 'forecast_closures')
                    );

                    $route = $submitFinal->isClicked() ? 'sales_forecasts_show' : 'sales_forecasts_add_cpr';

                    return $this->redirectToRoute($route, ['id' => Iri::id($salesForecast)]);
                } catch (ClientException $e) {
                    $this->violationMapper->mapToForm($e, $competitorPricingForm);
                }
            }

            return [
                'form' => $competitorPricingForm->createView(),
                'forecastClosure' => $forecastClosure,
                'salesForecast' => $salesForecast,
            ];
        }

        return [
            'salesForecast' => $salesForecast,
            'fcrForm' => $fcrForm->createView(),
        ];
    }

    #[Route(path: '/{salesForecastId}/files/{id}/delete', name: 'sales_forecast_delete_file', methods: ['GET'])]
    #[IsGranted(attribute: 'SALES_FORECAST_EDIT_VOTER', subject: new Expression('args["salesForecast"].getIri()'))]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'salesForecastId'])] ApiData $salesForecast, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_sales_forecast_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('sales_forecasts_show', ['id' => $salesForecast['id']]);
        }
        $this->fileManager->deleteFile($salesForecast, self::RESOURCE_URL, \sprintf('files/%s', $id));

        return $this->redirectToRoute('sales_forecasts_show', ['id' => $salesForecast['id']]);
    }

    /**
     * @return StreamedResponse
     */
    #[Route(path: '/{salesForecastId}/files/{id}', name: 'sales_forecast_files_show', methods: 'GET')]
    #[IsGranted(attribute: 'SALES_FORECAST_ACCESS_VOTER', subject: new Expression('args["salesForecast"].getIri()'))]
    public function showFile(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'salesForecastId'])] ApiData $salesForecast, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('sales/sales_forecasts/%s/files/%s', $salesForecast['id'], $id));
    }

    #[Route(path: '/export', name: 'sales_forecasts_export', methods: ['GET|POST'])]
    #[Template('sales/sales_forecasts/export.html.twig')]
    public function exportSalesForecasts(Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed('sfr_export',
                SalesForecastExportType::class,
                ['createdAt' => ['after' => (new \DateTime('1 year ago'))->format(DatePickerType::DEFAULT_INPUT_FORMAT)]]
            );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $parameters = [
                'normalization_groups_override' => ['sfr_export'],
                'context' => [
                    'datetime_format' => 'Y-m',
                    'csv_headers_enabled' => true,
                ],
                'properties' => [
                    'id',
                    'status',
                    'createdAt',
                    'endUser' => ['name'],
                    'buyer' => ['name'],
                    'eQuoteId',
                    'country' => ['name'],
                    'airport' => ['code'],
                    'factory' => ['name'],
                    'sso' => ['name', 'currency'],
                    'asm',
                    'product' => ['name'],
                    'tier' => ['name'],
                    'quantity',
                    'margin',
                    'price',
                    'customerSuccessPercentage',
                    'successPercentage',
                    'totalSuccessPercentage',
                    'estimatedSaleDate',
                    'lastCommentedAt',
                    'lastComment',
                ],
            ];

            $parameters = array_merge($parameters, array_filter($data, static function ($field) {
                return null !== $field && !empty($field);
            }));

            unset($parameters['format']);

            switch ($data['format']) {
                case 'csv':
                default:
                    return $this->csvStreamedResponseFactory->create(self::RESOURCE_URL, $parameters, 'sales_forecasts.csv');
                case 'xlsx':
                    $parameters['pagination'] = false;
                    unset($parameters['context'], $parameters['normalization_groups_override'], $parameters['properties']);
                    $parameters['columns'] = 'id,status,createdAt,endUser,buyer,country,airport,factory,sso,sso.currency,asm,product,tier,quantity,margin,price,customerSuccessPercentage,successPercentage,totalSuccessPercentage,estimatedSaleDate,lastCommentedAt,lastComment,equoteId,quote';

                    return $this->fileStreamedResponseFactory->create(
                        self::RESOURCE_URL,
                        ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
                        'sales_forecasts.xlsx'
                    );
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/transfer', name: 'sales_forecasts_transfer', methods: ['GET|POST'])]
    #[Template('sales/sales_forecasts/transfer.html.twig')]
    #[IsGranted('FEATURE_SALES_FORECAST_TRANSFER')]
    public function transferSalesForecasts(Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed('transfer',
                SalesForecastTransferType::class
            );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this->client->request('sales/sales_forecasts/transfer', null, null, 'POST',
                    [
                        'json' => $data,
                    ]
                );

                $this->addFlash(
                    'success',
                    $this->translator->trans('sales_forecasts.messages.success.transfer', [], 'sales_forecasts')
                );

                return $this->redirectToRoute('sales_forecasts_list', [
                    'search' => true,
                    'sso' => $data['sso'],
                    'country' => $data['country'],
                    'asm' => $data['asmTarget'],
                    'buyer' => $data['buyer'],
                    'endUser' => $data['endUser'],
                ]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
