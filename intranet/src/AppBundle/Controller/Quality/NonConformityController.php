<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Http\ZipStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Quality\NonConformityDataTableType;
use AppBundle\Filters\Type\Quality\NonConformity\NonConformityFilterType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Quality\NonConformity\NonConformityEquipmentRecordType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Form\Type\SimpleSearchType;
use AppBundle\Form\Type\StatusChoiceType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as SecurityService;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/non-conformities', defaults: ['alvest_module' => 'NCR', 'moduleDomain' => 'non_conformity'])]
class NonConformityController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    final public const NON_CONFORMITY_URL = 'quality/non_conformities';

    final public const NON_CONFORMITY_FILE_URL = 'non_conformity_files';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class, ViolationMapper::class,
            TranslatorInterface::class,
            FileManager::class,
            FileStreamedResponseFactory::class,
            FormFactoryInterface::class,
            ZipStreamedResponseFactory::class,
            SecurityService::class,
            ChartBuilderFactory::class,
        ]);
    }

    #[Route(path: '', name: 'non_conformity_home', methods: ['GET|POST'])]
    #[Template('quality/non_conformity/home.html.twig')]
    public function home(Request $request)
    {
        $datatable = $this->createDataTable(NonConformityDataTableType::class, NonConformityDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        $parameters = ['itemsPerPage' => 10];
        $filtered = false;
        $client = $this->container->get(Client::class);
        $idSearchForm = $this->createForm(IdSearchType::class, null, ['id_label' => false, 'id_placeholder' => 'By ID'])->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::NON_CONFORMITY_URL, $id));

                return $this->redirectToRoute('non_conformity_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('non_conformity.errors.not_exist', ['%id%' => $id], 'non_conformity'));
            }
        }
        // Search action
        $simpleSearchForm = $this->createForm(SimpleSearchType::class, [], [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'search_label' => false,
            'search_placeholder' => 'Search for...',
        ])->handleRequest($request);

        if ($simpleSearchForm->isSubmitted() && $simpleSearchForm->isValid()) {
            $filtered = true;
            $parameters = array_merge($parameters, $simpleSearchForm->getData());
            $parameters['itemsPerPage'] = 500;
        }

        $filters = $request->query->all('filter_non_conformity');
        if (isset($filters['location']['value']) && 'ALL' === $filters['location']['value']) {
            unset($filters['location']);
        }
        if (isset($filters['status']['value']) && 'ALL' === $filters['status']['value']) {
            unset($filters['status']);
        }
        $request->query->set('filter_non_conformity', $filters);

        $report = $client->get('reports/resource=/quality/non_conformities;x=location.name;y=status');

        $report['yTotals'] = [
            'PENDING' => $report['yTotals']['PENDING'] ?? 0,
            'IN PROGRESS' => $report['yTotals']['IN PROGRESS'] ?? 0,
            'SUSPENDED' => $report['yTotals']['SUSPENDED'] ?? 0,
        ];

        foreach ($report['rows'] as $key => &$row) {
            $row = [
                'PENDING' => $row['PENDING'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'PENDING', 'value' => 0],
                'IN PROGRESS' => $row['IN PROGRESS'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'IN PROGRESS', 'value' => 0],
                'SUSPENDED' => $row['SUSPENDED'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'SUSPENDED', 'value' => 0],
            ];
        }

        return [
            'filtered' => $filtered,
            'nonConformityDatatable' => $datatable->createView(),
            'form_id' => $idSearchForm->createView(),
            'form_search' => $simpleSearchForm->createView(),
            'reportTitle' => $filtered ? 'non_conformity.title.filtered' : 'non_conformity.title.last_ones',
            'nonConformities' => $client->findBy(self::NON_CONFORMITY_URL, $parameters, ['id' => 'desc']),
            'byStatusByLocation' => $report,
        ];
    }

    #[Route(path: '/report', name: 'non_conformity_report', methods: ['GET', 'POST'], defaults: ['label' => 'sidebar.common.reports', 'domain' => 'sidebar'])]
    #[Template('quality/non_conformity/report.html.twig')]
    public function report(Request $request)
    {
        $formFilter = $this->container->get(FormFactoryInterface::class)->createNamed('', NonConformityFilterType::class, [],
            [
                'action' => $this->generateUrl('non_conformity_report'),
                'method' => Request::METHOD_GET,
                'locationRequired' => true,
            ]
        );

        $topTenSupplier = [];

        $options = [];
        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = $formFilter->getData();
            $options = ['location' => $parameters['location']];
            if (null !== ($parameters['createdAt']['after'] ?? null)) {
                $options['from'] = $parameters['createdAt']['after'];
            }
            if (null !== ($parameters['createdAt']['before'] ?? null)) {
                $options['to'] = $parameters['createdAt']['before'];
            }
            if (null !== ($parameters['processes'] ?? null)) {
                $options['processes'] = $parameters['processes'];
            }

            $report = $this->container->get(Client::class)->get(
                'reports/resource=/quality/non_conformities;x=top_ten;y=supplier',
                ['query' => ['options' => $options]]
            );

            foreach ($report['yTotals'] as $key => $value) {
                $topTenSupplier[] = [
                    'supplierName' => explode(' - ', (string) $key)[0],
                    'supplierNumber' => explode(' - ', (string) $key)[1],
                    'supplierId' => explode(' - ', (string) $key)[2],
                    'value' => $value,
                ];
            }

            usort($topTenSupplier, static fn ($a, $b) => $b['value'] <=> $a['value']);
        }

        $location = null;
        if (!empty($options['location'])) {
            $location = $this->container->get(Client::class)->get($options['location']);
        }

        return [
            'topTenSupplier' => $topTenSupplier,
            'form' => $formFilter->createView(),
            'from' => !empty($options['from']) ? new \DateTime($options['from']) : null,
            'to' => !empty($options['to']) ? new \DateTime($options['to']) : null,
            'processes' => !empty($options['processes']) ? $options['processes'] : null,
            'location' => $location,
        ];
    }

    #[Route(path: '/report-part', name: 'non_conformity_report_part', methods: ['GET', 'POST'], defaults: ['label' => 'non_conformity.title.report_part', 'domain' => 'non_conformity'])]
    #[Template('quality/non_conformity/report_part.html.twig')]
    public function reportPart(Request $request)
    {
        $formFilter = $this->container->get(FormFactoryInterface::class)->createNamed('', NonConformityFilterType::class, [],
            [
                'action' => $this->generateUrl('non_conformity_report_part'),
                'method' => Request::METHOD_GET,
                'enable_location_attributes' => true,
            ]
        );

        $chartBuilder = null;
        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = $formFilter->getData();
            $limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 10;
            $parameters['limit'] = \in_array($limit, [10, 20, 30], true) ? $limit : 10;

            $report = $this->container->get(Client::class)->get(
                'reports/resource=/quality/non_conformities;x=top;y=part_number',
                ['query' => ['options' => $parameters]]
            );

            $xLabels = [];
            $yValues = [];

            if (!empty($report['rows']) && \is_array($report['rows'])) {
                foreach ($report['rows'] as $partNumber => $descriptions) {
                    if (!\is_array($descriptions)) {
                        continue;
                    }

                    foreach ($descriptions as $cell) {
                        if (!\is_array($cell) || !isset($cell['value'], $cell['y'])) {
                            continue;
                        }

                        $value = (float) $cell['value'];

                        if ($value > 0) {
                            $xLabels[] = \sprintf('%s - %s', $partNumber, $cell['y']);
                            $yValues[] = $value;
                            break;
                        }
                    }
                }

                array_multisort($yValues, \SORT_DESC, $xLabels);
            }

            $chartBuilder = $this->container->get(ChartBuilderFactory::class)
                ->getColumnChartBuilder()
                ->setTitle($this->container->get(TranslatorInterface::class)->trans('non_conformity.title.report_part_top', ['%limit%' => $parameters['limit']], 'non_conformity'))
                ->shareTooltip()
                ->addYAxis()
            ;
            foreach ($xLabels as $index => $label) {
                $chartBuilder->addPlot($this->container->get(TranslatorInterface::class)->trans('non_conformity.title.part_used', [], 'non_conformity'), $label, $yValues[$index]);
            }
        }

        return [
            'form' => $formFilter->createView(),
            'chartBuilder' => null !== $chartBuilder ? $chartBuilder->buildConfig(false) : null,
        ];
    }

    #[Route(path: '/{id}/show', name: 'non_conformity_show', methods: ['GET', 'POST'])]
    #[Template('quality/non_conformity/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_URL])] ApiData $nonConformity, Request $request)
    {
        $formFiles = $this->container->get('form.factory')->createNamed('form_files', SimpleFileType::class, [], ['display_public' => true]);

        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->container->get(FileManager::class)->uploadFile(
                        $nonConformity,
                        $file,
                        self::NON_CONFORMITY_URL,
                        $formFiles->get('description')->getData(),
                        'files',
                        true,
                        $formFiles->get('public')->getData(),
                    );
                }

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('files.upload_success', [], 'messages'));

                return $this->redirectToRoute('non_conformity_show', ['id' => $nonConformity->getIriId(), 'tab' => 'files']);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $formFiles);
            }
        }

        $formER = $this->container->get('form.factory')->createNamed('form_er', NonConformityEquipmentRecordType::class, $nonConformity);

        $formER->handleRequest($request);
        if ($formER->isSubmitted()) {
            try {
                // When react select is empty, Symfony request returning an array with an empty value, don't know why...
                $equipmentRecords = empty($request->request->all('form_er')['equipmentRecords'][0]) ? [] : $request->request->all('form_er')['equipmentRecords'];
                $this->container->get(Client::class)->save(self::NON_CONFORMITY_URL, ['@id' => $nonConformity->getIri(), 'equipmentRecords' => $equipmentRecords]);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('non_conformity.success.edition', [], 'non_conformity'));

                return $this->redirectToRoute('non_conformity_show', ['id' => $nonConformity->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $formFiles);
            }
        }
        $crabFilescount = 0;
        if (!empty($nonConformity['crabs'])) {
            $crabsWithOnlyPublicFiles = [];
            foreach ($nonConformity['crabs'] as $crab) {
                $crab['files'] = array_filter($crab['files'], static function ($file) {
                    return $file['public'];
                });
                $crabFilescount += \count($crab['files']);
                $crabsWithOnlyPublicFiles[] = $crab;
            }
            $nonConformity['crabs'] = $crabsWithOnlyPublicFiles;
        }

        return [
            'form_files' => $formFiles->createView(),
            'form_er' => $formER->createView(),
            'nonConformity' => $nonConformity,
            'filesBadgeCount' => \count($nonConformity['files']) + $crabFilescount,
            'status_form' => $this->createForm(StatusChoiceType::class, null, ['choices' => ['PENDING', 'IN PROGRESS', 'SUSPENDED', 'REJECTED', 'CLOSED'], 'id' => $nonConformity->getIriId(), 'route' => 'non_conformity_status'])->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', requirements: ['id' => '\d+'], name: 'non_conformity_delete', methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_NON_CONFORMITY_DELETE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::NON_CONFORMITY_URL, $id);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('non_conformity.success.delete', [], 'non_conformity'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('non_conformity.errors.delete', [], 'non_conformity'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('non_conformity_home');
    }

    #[Route(path: '/{id}/parts/{partId}/delete', name: 'non_conformity_part_delete', methods: ['GET'])]
    public function deletePart(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_URL])] ApiData $nonConformity, int $partId): RedirectResponse
    {
        $parts = [];
        foreach ($nonConformity['parts'] as $part) {
            if ($part['id'] !== $partId) {
                $parts[] = $part['@id'];
            }
        }

        try {
            $this->container->get(Client::class)->save(self::NON_CONFORMITY_URL, ['@id' => $nonConformity->getIri(), 'parts' => $parts]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.success.remove_part', [], 'vendor_warranty_claim'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.errors.part_delete', [], 'vendor_warranty_claim'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('non_conformity_show', ['id' => $nonConformity['id']]);
    }

    #[Route(path: '/{id}/models/{modelId}/delete', name: 'non_conformity_model_delete', methods: ['GET'])]
    public function deleteModel(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_URL])] ApiData $nonConformity, int $modelId): RedirectResponse
    {
        $models = [];
        foreach ($nonConformity['products'] as $model) {
            if (Iri::id($model) !== (string) $modelId) {
                $models[] = $model['@id'];
            }
        }

        try {
            $this->container->get(Client::class)->save(self::NON_CONFORMITY_URL, ['@id' => $nonConformity->getIri(), 'products' => $models]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('non_conformity.success.delete_model', [], 'non_conformity'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('non_conformity.errors.delete_model', [], 'non_conformity'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('non_conformity_show', ['id' => $nonConformity['id']]);
    }

    #[Route(path: '/{id}/edit', name: 'non_conformity_edit', methods: ['GET', 'POST'])]
    #[Template('quality/non_conformity/edit.html.twig')]
    #[IsGranted(attribute: 'NON_CONFORMITY_EDIT_VOTER', subject: new Expression('args["nonConformity"].getIri()'))]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_URL])] ApiData $nonConformity)
    {
        return [
            'nonConformity' => $nonConformity,
        ];
    }

    #[Route(path: '/{nonConformityId}/files/{id}/delete', name: 'non_conformity_file_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_NON_CONFORMITY_FILE_DELETE')]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_URL, 'id' => 'nonConformityId'])] ApiData $nonConformity, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_ncr_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('non_conformity_show', ['id' => $nonConformity->getIriId()]);
        }
        $this->container->get(FileManager::class)->deleteFile($nonConformity, self::NON_CONFORMITY_URL, \sprintf('files/%s', $id));
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('delete.success', [], 'file_type'));

        return $this->redirectToRoute('non_conformity_show', ['id' => $nonConformity->getIriId()]);
    }

    #[Route(path: '/{nonConformityId}/files/{id}', requirements: ['id' => '\d+'], name: 'non_conformity_files_show', methods: 'GET')]
    public function showFile($nonConformityId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::NON_CONFORMITY_URL, $nonConformityId, $id));
    }

    #[Route(path: '/{id}/admin-parts', name: 'non_conformity_admin_parts', requirements: ['nonConformityId' => '\d+'], methods: ['GET'], defaults: ['label' => 'non_conformity.title.edit_parts'])]
    #[Template('quality/non_conformity/parts.html.twig')]
    public function adminParts(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_URL])] ApiData $nonConformity)
    {
        return [
            'nonConformity' => $nonConformity,
            'props' => [
                'object' => $nonConformity->toArray(),
                'module' => 'NCR',
                'redirectUrl' => $this->generateUrl('non_conformity_show', ['id' => Iri::id($nonConformity)]),
            ],
        ];
    }

    #[Route(path: '/{nonConformityId}/files/download-all', requirements: ['nonConformityId' => '\d+'], name: 'non_conformity_zip_files', methods: 'GET')]
    public function showAllFile(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_URL, 'id' => 'nonConformityId'])] ApiData $nonConformity)
    {
        return $this->container->get(ZipStreamedResponseFactory::class)->create(\sprintf('%s/%d/files', self::NON_CONFORMITY_URL, $nonConformity['id']), \sprintf('NCR%s', $nonConformity['id']));
    }

    #[Route(path: '/add', name: 'non_conformity_add', methods: ['GET', 'POST'])]
    #[Template('quality/non_conformity/add.html.twig')]
    public function add(Request $request)
    {
        return [
            'crabId' => $request->query->get('crab'),
            'faqId' => $request->query->get('faq'),
        ];
    }

    #[Route(path: '/{id}/status/{status}', name: 'non_conformity_status', methods: ['GET', 'POST'])]
    public function changeStatus(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_URL])] ApiData $nonConformity, $status): RedirectResponse
    {
        try {
            $nonConformity = $this->container->get(Client::class)->save(self::NON_CONFORMITY_URL, ['@id' => $nonConformity->getIri(), 'status' => $status]);

            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('non_conformity.success.status', [], 'non_conformity'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash('error', \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('non_conformity.errors.status', [], 'non_conformity'), $errorDescription['hydra:description']));
        }

        return $this->redirectToRoute('non_conformity_show', ['id' => $nonConformity['id']]);
    }

    #[Route(path: '/{id}/main-file/{fileId}', requirements: ['id' => '\d+'], name: 'non_conformity_download_main_file', methods: 'GET')]
    public function showMainFile($id, $fileId)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/main_file/%s', self::NON_CONFORMITY_URL, $id, $fileId));
    }

    #[Route(path: '/{nonConformityId}/files/{fileId}/change_visibility', name: 'non_conformity_file_change_visibility', methods: 'GET')]
    public function changeFileVisibility(#[ApiValueResolverAttribute(parameters: ['resource' => self::NON_CONFORMITY_FILE_URL, 'id' => 'fileId'])] ApiData $nonConformityFile, int $fileId, int $nonConformityId): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->put(
                \sprintf('files/%d', $fileId),
                ['json' => ['fileId' => $fileId, 'public' => true !== $nonConformityFile['public']]]
            );

            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('visibility.success', [], 'file_type'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash('error', \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('visibility.errors', [], 'file_type'), $errorDescription['hydra:description']));
        }

        return $this->redirectToRoute('non_conformity_show', ['id' => $nonConformityId]);
    }
}
