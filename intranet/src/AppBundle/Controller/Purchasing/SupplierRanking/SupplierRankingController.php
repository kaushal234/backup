<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\SupplierRanking;

use ActivityBundle\Form\Type\CommentType;
use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Chart\Purchasing\SupplierRanking\RankingRadarChartBuilder;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Purchasing\SupplierRankingDataTableType;
use AppBundle\Form\Type\Purchasing\SupplierRanking\SupplierRankingReportCriteriaType;
use AppBundle\Form\Type\Purchasing\SupplierRanking\SupplierRankingType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(attribute: new Expression("is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL') or (is_granted('FEATURE_SUPPLIER_RANKING_READ') and is_granted('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ') and is_granted('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ') and is_granted('FEATURE_SUPPLIER_RANKING_CRITERIA_READ'))"))]
#[Route(path: '/purchasing/supplier-rankings', defaults: ['alvest_module' => 'SRM', 'breadcrumb_label' => 'menu.supplier_ranking.title', 'moduleDomain' => 'supplier_rankings'])]
class SupplierRankingController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    private const string RESOURCE_URL = 'purchasing/supplier_ranking/';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            FormFactoryInterface::class,
            ViolationMapper::class,
            TranslatorInterface::class,
            FileStreamedResponseFactory::class,
        ]);
    }

    #[Template('purchasing/supplier_ranking/index.html.twig')]
    #[Route(path: '/', name: 'supplier_rankings_home')]
    public function index(Request $request)
    {
        $client = $this->container->get(Client::class);
        $criterias = $client->findAll(self::RESOURCE_URL.'criterias', [], ['name']);
        $classifications = $client->findAll(self::RESOURCE_URL.'classifications', [], ['name']);

        $datatable = $this->createDataTable(
            SupplierRankingDataTableType::class,
            SupplierRankingDataTableType::RESOURCE,
            [
                'criterias' => $criterias,
                'classifications' => $classifications,
            ]
        );

        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'datatable' => $datatable->createView(),
        ];
    }

    #[Template('purchasing/supplier_ranking/show.html.twig')]
    #[Route(path: '/{id}', name: 'supplier_rankings_show')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'purchasing/supplier_ranking/supplier_rankings'])] ApiData $supplierRanking, ChartBuilderFactory $chartBuilderFactory)
    {
        $client = $this->container->get(Client::class);
        $thresholds = $client->findBy(self::RESOURCE_URL.'thresholds', [
            'expertiseLevel' => $supplierRanking['expertiseLevel']['@id'],
            'showInGraph' => true,
        ], ['classification.workflowLevel' => 'DESC']);
        $criterias = $client->findAll(self::RESOURCE_URL.'criterias', [], ['name']);
        $chartBuilder = new RankingRadarChartBuilder($supplierRanking['notations'], $thresholds->all());
        $chartBuilder->setTitle($this->container->get(TranslatorInterface::class)->trans('chart.title', [], 'supplier_ranking'));

        return [
            'supplierRanking' => $supplierRanking,
            'chart' => $chartBuilder->buildConfig(),
            'criterias' => $criterias,
            'commentForm' => $this->container->get('form.factory')->createNamed('comment', CommentType::class)->createView(),
        ];
    }

    #[Route(path: '/by-code/{code}', name: 'supper_rankings_by_code', methods: ['GET'])]
    public function byCode(string $code): RedirectResponse
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        try {
            $supplierRanking = $client->findOneBy(self::RESOURCE_URL.'supplier_rankings', ['supplier.code' => $code]);

            return $this->redirectToRoute('supplier_rankings_show',
                ['id' => $supplierRanking['id']]);
        } catch (\RangeException $e) {
            $this->addFlash('error', $translator->trans('messages.error.supplier_not_found', [], 'supplier_ranking'));

            return $this->redirectToRoute('supplier_rankings_home');
        }
    }

    #[Template('purchasing/supplier_ranking/edit.html.twig')]
    #[Route(path: '/{id}/edit', name: 'supplier_rankings_edit')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => 'purchasing/supplier_ranking/supplier_rankings', 'filters' => ['normalization_groups' => ['supplier_ranking:update']]])] ApiData $supplierRanking, Request $request)
    {
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);
        $criterias = $client->findAll(self::RESOURCE_URL.'criterias', [], ['name']);
        $supplierRanking = $supplierRanking->toArray();

        $newNotations = [];
        foreach ($criterias as $criteria) {
            $notationFoundFlag = false;
            foreach ($supplierRanking['notations'] as $notation) {
                if ($notation['criteria']['id'] === $criteria['id']) {
                    $newNotations[] = $notation;
                    $notationFoundFlag = true;
                }
            }
            if (false === $notationFoundFlag) {
                $newNotations[] = [
                    'criteria' => $criteria,
                    'notation' => null,
                ];
            }
        }
        $supplierRanking['notations'] = $newNotations;

        $form = $formFactory->create(SupplierRankingType::class, array_merge($supplierRanking, [
            'expertiseLevel' => $supplierRanking['expertiseLevel']['@id'],
        ]));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                foreach ($data['notations'] as &$notation) {
                    $notation['criteria'] = $notation['criteria']['@id'];
                }
                $client->save($data['@id'], $data);

                $this->addFlash(
                    'success',
                    $translator->trans('messages.success.edit', [], 'supplier_ranking')
                );

                return $this->redirectToRoute('supplier_rankings_show', ['id' => $supplierRanking['id']]);
            } catch (ClientException $e) {
                $violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'supplierRanking' => $supplierRanking,
            'form' => $form->createView(),
            'criterias' => $client->findAll(self::RESOURCE_URL.'criterias', [], ['name']),
        ];
    }

    public function export($parameters)
    {
        $fileStreamedResponseFactory = $this->container->get(FileStreamedResponseFactory::class);

        $parameters['itemsPerPage'] = 40000;
        $parameters['columns'] = 'supplierNumber,supplierName,supplier.country,supplier.location,supplier.masterBuyer,expertiseLevel.name,revenue,supplier.currency,cost,logistic,communicationTransparencyResponsiveness,productFieldSupport,environmentalSocialGovernance,antiCorruption,cybersecurity,classification.name,isSupplierApproved,lastReviewAt,lastReviewBy,nextReviewAt,lastScreeningAt,lastScreeningBy';

        return $fileStreamedResponseFactory->create(
            self::RESOURCE_URL.'supplier_rankings',
            ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            'supplier_rankings.xlsx'
        );
    }

    #[Template('purchasing/supplier_ranking/criteria.html.twig')]
    #[Route(path: '/report/criteria', name: 'supplier_rankings_criteria', defaults: ['label' => 'criterias.title', 'domain' => 'supplier_ranking'])]
    public function criteria(Request $request)
    {
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $location = $request->query->get('location');

        $form = $formFactory->create(
            SupplierRankingReportCriteriaType::class,
            [
                'location' => $location ? '/locations/'.$location : null,
                'classification' => [
                    '/purchasing/supplier_ranking/classifications/1',
                    '/purchasing/supplier_ranking/classifications/2',
                    '/purchasing/supplier_ranking/classifications/3',
                    '/purchasing/supplier_ranking/classifications/4',
                ],
            ]
        );

        $antiCorruptionCount = 0;
        $cybersecurityCount = 0;
        $esgCount = 0;
        $showValues = false;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $showValues = true;
                $response = $client->findAll(self::RESOURCE_URL.'supplier_rankings', $form->getData());
                foreach ($response as $supplierRanking) {
                    foreach ($supplierRanking['notations'] as $notation) {
                        if (7 === $notation['criteria']['id'] && null === $notation['notation']) {
                            ++$esgCount;
                        }
                        if (8 === $notation['criteria']['id'] && null === $notation['notation']) {
                            ++$antiCorruptionCount;
                        }
                        if (9 === $notation['criteria']['id'] && null === $notation['notation']) {
                            ++$cybersecurityCount;
                        }
                    }
                }
            } catch (ClientException $e) {
                $this->addFlash('error', $translator->trans('common.error.server'));
                $violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'esgCount' => $esgCount,
            'antiCorruptionCount' => $antiCorruptionCount,
            'cybersecurityCount' => $cybersecurityCount,
            'showValues' => $showValues,
            'formValues' => $form->getData(),
        ];
    }

    #[Template('purchasing/supplier_ranking/review.html.twig')]
    #[Route(path: '/review/show', name: 'supplier_rankings_review')]
    public function review(Request $request)
    {
        $client = $this->container->get(Client::class);
        $criterias = iterator_to_array($client->findAll(
            self::RESOURCE_URL.'criterias',
            [],
            ['name'],
            ['cache' => true]
        ));

        $allVendorsDataTable = $this->createNamedDataTable(
            'all_vendors_data_table',
            SupplierRankingDataTableType::class,
            SupplierRankingDataTableType::RESOURCE,
            [
                'criterias' => $criterias,
                'title' => 'supplier_ranking.data_table.all_vendors',
            ]
        );

        $allVendorsDataTable->handleRequest($request);

        if ($allVendorsDataTable->isExporting() && $allVendorsDataTable->getQuery() instanceof ApiProxyQuery) {
            return $allVendorsDataTable->getQuery()->export();
        }

        if ($allVendorsDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($allVendorsDataTable);
        }

        $limitedVendorsDataTable = $this->createNamedDataTable(
            'limited_vendors_data_table',
            SupplierRankingDataTableType::class,
            SupplierRankingDataTableType::RESOURCE,
            [
                'criterias' => $criterias,
                'title' => 'supplier_ranking.data_table.limited_vendors',
            ]
        );

        $limitedVendorsDataTable->handleRequest($request);

        if ($limitedVendorsDataTable->isExporting() && $limitedVendorsDataTable->getQuery() instanceof ApiProxyQuery) {
            return $limitedVendorsDataTable->getQuery()->export();
        }

        if ($limitedVendorsDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($limitedVendorsDataTable);
        }

        $monitoredVendorsDataTable = $this->createNamedDataTable(
            'monitored_vendors_data_table',
            SupplierRankingDataTableType::class,
            SupplierRankingDataTableType::RESOURCE,
            [
                'criterias' => $criterias,
                'title' => 'supplier_ranking.data_table.monitored_vendors',
            ]
        );

        $monitoredVendorsDataTable->handleRequest($request);

        if ($monitoredVendorsDataTable->isExporting() && $monitoredVendorsDataTable->getQuery() instanceof ApiProxyQuery) {
            return $monitoredVendorsDataTable->getQuery()->export();
        }

        if ($monitoredVendorsDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($monitoredVendorsDataTable);
        }

        return [
            'allVendorsDataTable' => $allVendorsDataTable->createView(),
            'limitedVendorsDataTable' => $limitedVendorsDataTable->createView(),
            'monitoredVendorsDataTable' => $monitoredVendorsDataTable->createView(),
        ];
    }
}
