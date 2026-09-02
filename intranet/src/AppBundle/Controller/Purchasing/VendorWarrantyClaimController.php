<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Quality\NonConformityController;
use AppBundle\Controller\Quality\SupplierCorrectiveActionRequestController;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Purchasing\VendorWarrantyClaimsDataTableType;
use AppBundle\Filters\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimBuyerReportFilter;
use AppBundle\Filters\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimSupplierReportFilter;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimAddType;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimCommentType;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimEditType;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimFlopType;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimResolutionType;
use AppBundle\Manager\FileManager;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\IriResolver;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\StatusForm;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/vendor-warranty-claims', defaults: ['alvest_module' => 'VWC', 'moduleDomain' => 'vendor_warranty_claim'])]
class VendorWarrantyClaimController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    final public const VENDOR_WARRANTY_CLAIM_URL = 'purchasing/vendor_warranty_claims';
    final public const NCR_VENDOR_WARRANTY_CLAIM_URL = 'purchasing/ncr_vendor_warranty_claims';
    final public const WC_VENDOR_WARRANTY_CLAIM_URL = 'purchasing/wc_vendor_warranty_claims';
    final public const VENDOR_WARRANTY_CLAIM_STATUS_URL = 'purchasing/vendor_warranty_claim_statuses';
    final public const VENDOR_WARRANTY_CLAIM_FILE_URL = 'vendor_warranty_claim_files';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            ViolationMapper::class,
            TranslatorInterface::class,
            FileManager::class,
            FileStreamedResponseFactory::class,
            FormFactoryInterface::class,
            ChartBuilderFactory::class,
            CsvStreamedResponseFactory::class,
            IriResolver::class,
            StatusForm::class,
        ]);
    }

    #[Route(path: '', name: 'vendor_warranty_claim_home', methods: ['GET|POST'])]
    #[Template('purchasing/vendor_warranty_claim/home.html.twig')]
    public function home(Request $request)
    {
        $client = $this->container->get(Client::class);
        $idSearchForm = $this->createForm(IdSearchType::class, null, ['id_label' => false, 'id_placeholder' => 'By ID'])->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::VENDOR_WARRANTY_CLAIM_URL, $id));

                return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.errors.not_exist', ['%id%' => $id], 'vendor_warranty_claim'));
            }
        }

        $datatable = $this->createDataTable(VendorWarrantyClaimsDataTableType::class, self::VENDOR_WARRANTY_CLAIM_URL);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        $showClaimAmount = $request->query->getBoolean('show_claim_amount');
        $totalClaimAmount = 0;

        if ($showClaimAmount) {
            try {
                $filters = $request->query->all();
                $locationFilter = $filters['filter_vendor_warranty_claims']['location']['value'] ?? null;
                $locationId = $locationFilter ? (int) explode('/', trim($locationFilter, '/'))[1] : null;

                $result = $this->container->get(Client::class)->get(self::VENDOR_WARRANTY_CLAIM_URL.'_statistics', ['query' => ['location' => $locationId]]);
                $totalClaimAmount = $result['totalClaimAmount'] ?? 0;
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.messages.error.generic', [], 'vendor_warranty_claim'));
            }
        }

        return [
            'form_id' => $idSearchForm->createView(),
            'vendorWarrantyClaimsDatatable' => $datatable->createView(),
            'showClaimAmount' => $showClaimAmount,
            'totalClaimAmount' => $totalClaimAmount,
        ];
    }

    #[Route(path: '/matrix', name: 'vendor_warranty_claim_matrix', defaults: ['label' => 'vendor_warranty_claim.button.matrix', 'domain' => 'vendor_warranty_claim'], methods: ['GET|POST'])]
    #[Template('purchasing/vendor_warranty_claim/matrix.html.twig')]
    public function matrix(Request $request): array
    {
        $client = $this->container->get(Client::class);

        $byStatusByAssignee = false;
        $options = [];
        $translationKey = null;
        $xParam = null;
        $user = $client->get('/me');
        $locationIri = $user['businessUnit']['location']['@id'] ?? null;
        if (null !== $locationIri) {
            $translationKey = ($byDepartment = (bool) $request->query->get('byDepartment')) ? 'by_department' : 'by_assignee';
            $byStatusByAssignee = true;
            $xParam = $byDepartment ? 'filter_vendor_warranty_claims[department][value]' : 'filter_vendor_warranty_claims[assignee][value]';
            $options['options'] = [
                'location' => $locationIri,
                'by_department' => $byDepartment,
            ];
        }

        return [
            'byStatusByLocation' => $client->get('reports/resource=/purchasing/vendor_warranty_claims;x=location.name;y=status.name'),
            'byStatusByAssignee' => $byStatusByAssignee ? $client->get('reports/resource=/purchasing/vendor_warranty_claims;x=assignee.id;y=status.name', ['query' => $options]) : null,
            'translationKey' => $translationKey,
            'xParam' => $xParam,
            'userLocation' => $locationIri,
        ];
    }

    #[Route(path: '/report', name: 'vendor_warranty_claim_report', defaults: ['label' => 'sidebar.common.reports', 'domain' => 'sidebar'], methods: ['GET', 'POST'])]
    #[Template('purchasing/vendor_warranty_claim/report.html.twig')]
    public function report(Request $request)
    {
        $formSupplierFactory = $this->container->get(FormFactoryInterface::class)->createNamed('', VendorWarrantyClaimSupplierReportFilter::class, [],
            [
                'action' => $this->generateUrl('vendor_warranty_claim_report'),
                'method' => Request::METHOD_GET,
            ]
        );

        $formBuyer = $this->container->get(FormFactoryInterface::class)->createNamed('filter_buyer', VendorWarrantyClaimBuyerReportFilter::class, [],
            [
                'action' => $this->generateUrl('vendor_warranty_claim_report'),
                'method' => Request::METHOD_GET,
            ]
        );

        $filtered = false;
        $topPartFailure = [];
        $reportPartFailureHistory = [];
        $reportRecoveryCost = [];
        $reportSupplierHistory = [];
        $reportBuyerHistory = [];
        $reportAverageResolvedTime = [];
        $reportVendorWarrantyClaimResolved = [];
        $vendorWarrantyClaims = [];

        $client = $this->container->get(Client::class);

        $chartRecoveryCost = $this->container->get(ChartBuilderFactory::class)
            ->getColumnChartBuilder()
            ->addYAxis('Amount')
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.report.recovery_cost', [], 'vendor_warranty_claim'));

        $chartAverageResolvedTime = $this->container->get(ChartBuilderFactory::class)
            ->getColumnChartBuilder()
            ->addYAxis('Days')
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.report.average_resolved_time', [], 'vendor_warranty_claim'));

        $chartVendorWarrantyClaimResolved = $this->container->get(ChartBuilderFactory::class)
            ->getColumnChartBuilder()
            ->addYAxis('Percentage %')
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.report.resolved_rate', [], 'vendor_warranty_claim'));

        $formSupplierFactory->handleRequest($request);
        if ($formSupplierFactory->isSubmitted() && $formSupplierFactory->isValid()) {
            $filtered = true;
            $parameters = $formSupplierFactory->getData();
            $options['location'] = $parameters['location'];
            if (null !== $parameters['supplierNumber']) {
                $options['supplierNumber'] = $parameters['supplierNumber'];
                if (null !== $parameters['rejected']) {
                    $options['rejected'] = $parameters['rejected'];
                }
                $reportPartFailureHistory = $client->get(
                    'reports/resource=/purchasing/vendor_warranty_claims;x=part_failure_history;y=supplier',
                    ['query' => ['options' => $options]]
                );
                $reportSupplierHistory = $client->get(
                    'reports/resource=/purchasing/vendor_warranty_claims;x=supplier_history;y=created_at',
                    ['query' => ['options' => $options]]
                );

                $reportRecoveryCost = $client->get(
                    'reports/resource=/purchasing/vendor_warranty_claims;x=supplier_recovery_cost;y=created_at',
                    ['query' => ['options' => $options]]
                );
                foreach ($reportRecoveryCost['rows'] as $key => $data) {
                    foreach ($data as $credit => $value) {
                        $chartRecoveryCost->addPlot(
                            $credit,
                            $key,
                            $value['value']
                        );
                    }
                }

                $vendorWarrantyClaims = $client->findBy('/purchasing/vendor_warranty_claims', [
                    'status.name' => 'VENDOR_TO_RESPOND',
                    'location' => $options['location'],
                    'supplierNumber' => $options['supplierNumber'],
                ], ['requestedCreditAmount' => 'DESC']
                );
            }

            $reportPartFailure = $client->get(
                'reports/resource=/purchasing/vendor_warranty_claims;x=top_part_failure;y=location',
                ['query' => ['options' => $options]]
            );

            foreach ($reportPartFailure['yTotals'] as $key => $value) {
                $topPartFailure[] = [
                    'name' => $key,
                    'value' => $value,
                ];
            }
            $parameters['location'] = [$parameters['location']];
        }

        $formBuyer->handleRequest($request);
        if ($formBuyer->isSubmitted() && $formBuyer->isValid()) {
            $filtered = true;
            $parameters = $formBuyer->getData();
            $options = [];

            if (null !== $parameters['buyer']) {
                $options['buyer'] = $parameters['buyer'];
                $reportBuyerHistory = $client->get(
                    'reports/resource=/purchasing/vendor_warranty_claims;x=buyer_history;y=created_at',
                    ['query' => ['options' => $options]]
                );
            }

            if (null !== $parameters['location']) {
                $options['location'] = $parameters['location'];

                $reportAverageResolvedTime = $client->get(
                    'reports/resource=/purchasing/vendor_warranty_claims;x=resolved_time;y=buyer',
                    ['query' => ['options' => $options]]
                );

                foreach ($reportAverageResolvedTime['rows'] as $key => $data) {
                    foreach ($data as $buyer => $value) {
                        $chartAverageResolvedTime->addPlot(
                            $buyer,
                            $key,
                            $value['value']
                        );
                    }
                }

                $reportVendorWarrantyClaimResolved = $client->get(
                    'reports/resource=/purchasing/vendor_warranty_claims;x=buyer_vwc_closed;y=created_at',
                    ['query' => ['options' => $options]]
                );

                foreach ($reportVendorWarrantyClaimResolved['rows'] as $key => $data) {
                    foreach ($data as $buyer => $value) {
                        $chartVendorWarrantyClaimResolved->addPlot(
                            $buyer,
                            $key,
                            $value['value']
                        );
                    }
                }
            }
        }

        $parameters['twoYearsAgo'] = (new \DateTime('2 year ago'))->format('m/d/Y');

        return [
            'chartFactory' => $chartRecoveryCost->buildConfig(),
            'chartVendorWarrantyClaimResolved' => $chartVendorWarrantyClaimResolved->buildConfig(),
            'chartAverageResolvedTime' => $chartAverageResolvedTime->buildConfig(),
            'filtered' => $filtered,
            'topPartFailure' => $topPartFailure,
            'reportPartFailHistory' => $reportPartFailureHistory,
            'reportRecoveryCost' => $reportRecoveryCost,
            'reportSupplierHistory' => $reportSupplierHistory,
            'reportBuyerHistory' => $reportBuyerHistory,
            'reportAverageResolvedTime' => $reportAverageResolvedTime,
            'reportVendorWarrantyClaimResolved' => $reportVendorWarrantyClaimResolved,
            'formSupplierFactory' => $formSupplierFactory->createView(),
            'formBuyer' => $formBuyer->createView(),
            'parameters' => $parameters,
            'vendorWarrantyClaims' => $vendorWarrantyClaims,
        ];
    }

    #[Route(path: '/{id}/delete', requirements: ['id' => '\d+'], name: 'vendor_warranty_claim_delete', methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_VENDOR_WARRANTY_CLAIM_DELETE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::VENDOR_WARRANTY_CLAIM_URL, $id);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.success.delete', [], 'vendor_warranty_claim')
            );
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.errors.delete', [], 'vendor_warranty_claim'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('vendor_warranty_claim_home');
    }

    #[Route(path: '/{id}/parts/{partId}/delete', name: 'vendor_warranty_claim_part_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_VENDOR_WARRANTY_CLAIM_EDIT')]
    public function deletePart(#[ApiValueResolverAttribute(parameters: ['resource' => self::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim, int $partId): RedirectResponse
    {
        $parts = [];
        foreach ($vendorWarrantyClaim['parts'] as $part) {
            if ($part['id'] !== $partId) {
                $parts[] = $part['@id'];
            }
        }
        $iri = $this->container->get(IriResolver::class)->resolve($vendorWarrantyClaim);

        try {
            $this->container->get(Client::class)->save($iri, ['@id' => $vendorWarrantyClaim->getIri(), 'parts' => $parts]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.success.remove_part', [], 'vendor_warranty_claim'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.errors.part_delete', [], 'vendor_warranty_claim'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim['id']]);
    }

    #[Route(path: '/{id}/edit', name: 'vendor_warranty_claim_edit', methods: ['GET', 'POST'])]
    #[Template('purchasing/vendor_warranty_claim/edit.html.twig')]
    #[IsGranted(attribute: 'VENDOR_WARRANTY_CLAIM_VOTER', subject: new Expression('args["vendorWarrantyClaim"].getIri()'))]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim, Request $request)
    {
        $client = $this->container->get(Client::class);
        $authorizedFields = $client->get('/fields', ['query' => ['iri' => $vendorWarrantyClaim['@id'], 'method' => 'PUT']]);

        $form = $this->createForm(VendorWarrantyClaimEditType::class, $vendorWarrantyClaim, ['authorized_fields' => $authorizedFields]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            unset($data['currency']);
            $iri = $this->container->get(IriResolver::class)->resolve($vendorWarrantyClaim);

            try {
                $vwcSaved = $client->save($iri, $data);
                if (isset($data['mainFile']) && null !== $data['mainFile']) {
                    $this->container->get(FileManager::class)->uploadFile($vwcSaved, $data['mainFile'], self::WC_VENDOR_WARRANTY_CLAIM_URL, null, 'main_file', false, true);
                }

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.success.edition', [], 'vendor_warranty_claim'));

                return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
        ];
    }

    #[Route(path: '/{ncrId}/add-from-ncr', name: 'vendor_warranty_claim_add_from_ncr', methods: ['GET', 'POST'], defaults: ['label' => 'vendor_warranty_claim.title.add_from_ncr'])]
    #[Template('purchasing/vendor_warranty_claim/add.html.twig')]
    #[IsGranted('FEATURE_NCR_VENDOR_WARRANTY_CLAIM_CREATE')]
    public function addFromNCR(#[ApiValueResolverAttribute(parameters: ['resource' => NonConformityController::NON_CONFORMITY_URL, 'id' => 'ncrId'])] ApiData $nonConformity, Request $request)
    {
        $client = $this->container->get(Client::class);
        $user = $client->get('/me');
        $parts = [];
        $suppliers = [];
        $partsNotFound = [];
        foreach ($nonConformity['parts'] as $ncrPart) {
            try {
                $part = $this->container->get(Client::class)->get(\sprintf('ion/items/item=%s;site=%s', $ncrPart['partNumber'], $nonConformity['location']['erp']));
            } catch (ClientException $exception) {
                $partsNotFound[] = $ncrPart['partNumber'];
                continue;
            }
            $parts[] = [
                'partNumber' => $part['item'],
                'description' => $part['itemDescription'],
                'unitOfMeasure' => $part['unitOfMeasure'],
                'standardCost' => $part['standardPrice'],
                'quantity' => $ncrPart['quantity'],
                'serialNumber' => $ncrPart['serialNumber'],
            ];

            foreach ($part['lastSuppliers'] as $businessPartner) {
                $suppliers[\sprintf('%s - %s: Part Number %s', $businessPartner['code'], $businessPartner['name'], $part['item'])] = $businessPartner['code'];
            }

            if (!\in_array($part['businessPartner']['code'], $suppliers, true)) {
                $suppliers[\sprintf('%s - %s: Part Number %s', $part['businessPartner']['code'], $part['businessPartner']['name'], $part['item'])] = $part['businessPartner']['code'];
            }
        }
        $payload = [
            'location' => $user['businessUnit']['location']['@id'],
            'supplierNumber' => $nonConformity['supplierNumber'],
            'supplierName' => $nonConformity['supplierName'],
            'supplierErp' => $user['businessUnit']['location']['erp'],
            'requestedCreditAmount' => $nonConformity['cost'],
            'requestedSupplierAction' => $nonConformity['problem'],
            'parts' => $parts,
            'nonConformity' => $nonConformity['@id'],
        ];

        $form = $this->createForm(VendorWarrantyClaimAddType::class, $payload, ['suppliers' => $suppliers]);
        $vendorWarrantyClaim = $this->createVWCFromForm($form, $request, self::NCR_VENDOR_WARRANTY_CLAIM_URL);

        if (null !== $vendorWarrantyClaim) {
            return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim['id']]);
        }

        if ([] !== $partsNotFound) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.errors.part_not_fount', ['%part%' => implode(', ', $partsNotFound)], 'vendor_warranty_claim'));
        }

        return [
            'form' => $form->createView(),
            'origin' => $nonConformity,
        ];
    }

    #[Route(path: '/{warrantyClaimId}/add-from-wc', name: 'vendor_warranty_claim_add_from_wc', defaults: ['label' => 'vendor_warranty_claim.title.add_from_wc'], methods: ['GET', 'POST'])]
    #[Template('purchasing/vendor_warranty_claim/add.html.twig')]
    #[IsGranted('FEATURE_WC_VENDOR_WARRANTY_CLAIM_CREATE')]
    public function addFromWC(int $warrantyClaimId, Request $request)
    {
        $client = $this->container->get(Client::class);
        $user = $client->get('/me');
        $location = $client->findOneBy('locations', ['name' => $request->query->get('location')]);
        $parts = [];
        $suppliers = [];
        $partsNotFound = [];
        foreach (($request->query->all()['parts'] ?? []) as $partNumber => $quantity) {
            try {
                $part = $client->get(\sprintf('ion/items/item=%s;site=%s', $partNumber, $location['erp']));
            } catch (ClientException $exception) {
                $partsNotFound[] = $partNumber;
                continue;
            }
            $parts[] = [
                'partNumber' => $part['item'],
                'description' => $part['itemDescription'],
                'unitOfMeasure' => $part['unitOfMeasure'],
                'standardCost' => $part['standardPrice'],
                'quantity' => (float) $quantity,
            ];

            foreach ($part['lastSuppliers'] as $businessPartner) {
                $suppliers[\sprintf('%s - %s: Part Number %s', $businessPartner['code'], $businessPartner['name'], $part['item'])] = $businessPartner['code'];
            }

            if (!\in_array($part['businessPartner']['code'], $suppliers, true)) {
                $suppliers[\sprintf('%s - %s: Part Number %s', $part['businessPartner']['code'], $part['businessPartner']['name'], $part['item'])] = $part['businessPartner']['code'];
            }
        }

        $payload = [
            'location' => $user['businessUnit']['location']['@id'],
            'supplierErp' => $user['businessUnit']['location']['erp'],
            'parts' => $parts,
            'warrantyClaimId' => $warrantyClaimId,
        ];

        $form = $this->createForm(VendorWarrantyClaimAddType::class, $payload, ['suppliers' => $suppliers, 'addPhoto' => true]);
        $vendorWarrantyClaim = $this->createVWCFromForm($form, $request, self::WC_VENDOR_WARRANTY_CLAIM_URL);

        if (null !== $vendorWarrantyClaim) {
            return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim['id']]);
        }

        if (!empty($partsNotFound)) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.errors.part_not_fount', ['%part%' => implode(', ', $partsNotFound)], 'vendor_warranty_claim'));
        }

        return [
            'form' => $form->createView(),
            'origin' => $warrantyClaimId,
        ];
    }

    #[Route(path: '/{id}/internal-note', name: 'vendor_warranty_claim_internal_note', methods: ['GET', 'POST'], defaults: ['label' => 'vendor_warranty_claim.title.internal_note'])]
    #[Route(path: '/{id}/email-vendor', name: 'vendor_warranty_claim_email_vendor', methods: ['GET', 'POST'], defaults: ['label' => 'vendor_warranty_claim.title.email_vendor'])]
    #[Template('purchasing/vendor_warranty_claim/comment.html.twig')]
    public function comment(#[ApiValueResolverAttribute(parameters: ['resource' => self::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim, Request $request)
    {
        $internal = 'vendor_warranty_claim_internal_note' === $request->attributes->get('_route');
        $contacts = [];
        $posterEmail = $request->query->get('poster');
        $poster = [];
        if (!$internal) {
            try {
                $supplier = $this->container->get(Client::class)->get(\sprintf('%s/%s', SupplierCorrectiveActionRequestController::RESOURCE_URL_SUPPLIER, $vendorWarrantyClaim['supplierNumber']));
            } catch (\Exception $e) {
                $supplier = [];
            }

            foreach ($supplier['contacts'] ?? [] as $contact) {
                $key = \sprintf('%s (%s)', $contact['fullName'], $contact['emailAddress']);
                $contacts[$key] = $contact['emailAddress'];
                if ($posterEmail === $contact['emailAddress']) {
                    $poster[$key] = $contact['emailAddress'];
                }
            }
        }
        $form = $this->createForm(VendorWarrantyClaimCommentType::class, [], ['internal' => $internal, 'contacts' => $contacts, 'poster' => $poster])->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save('/comments', array_merge($form->getData(), ['resource' => $vendorWarrantyClaim->getIri()]));
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.success.comment', [], 'vendor_warranty_claim'));

                return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
        ];
    }

    #[Route(path: '/{vendorWarrantyClaimId}/files/{id}/delete', name: 'vendor_warranty_claim_file_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_VENDOR_WARRANTY_CLAIM_FILE_DELETE')]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::VENDOR_WARRANTY_CLAIM_URL, 'id' => 'vendorWarrantyClaimId', 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_vwc_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim->getIriId()]);
        }
        $this->container->get(FileManager::class)->deleteFile($vendorWarrantyClaim, self::VENDOR_WARRANTY_CLAIM_URL, \sprintf('files/%s', $id));
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('files.delete_success', [], 'messages'));

        return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim->getIriId()]);
    }

    #[Route(path: '/{vendorWarrantyClaimId}/files/{id}', name: 'vendor_warranty_claim_files_show', methods: 'GET')]
    public function showFile($vendorWarrantyClaimId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::VENDOR_WARRANTY_CLAIM_URL, $vendorWarrantyClaimId, $id));
    }

    #[Route(path: '/{id}/admin-parts', name: 'vendor_warranty_claim_admin_parts', requirements: ['id' => '\d+'], methods: ['GET'], defaults: ['label' => 'non_conformity.title.edit_parts', 'domain' => 'non_conformity'])]
    #[Template('purchasing/vendor_warranty_claim/parts.html.twig')]
    #[IsGranted(attribute: 'VENDOR_WARRANTY_CLAIM_VOTER', subject: new Expression('args["vendorWarrantyClaim"].getIri()'))]
    public function adminParts(#[ApiValueResolverAttribute(parameters: ['resource' => self::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim)
    {
        return [
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
            'props' => [
                'object' => $vendorWarrantyClaim->toArray(),
                'module' => 'VWC',
                'redirectUrl' => $this->generateUrl('vendor_warranty_claim_show', ['id' => Iri::id($vendorWarrantyClaim)]),
            ],
        ];
    }

    #[Route(path: '/{id}/status/{status}', name: 'vendor_warranty_claim_status', methods: ['GET', 'POST'], defaults: ['label' => 'display.table.purchase_order_line.actions.close', 'domain' => 'messages'])]
    #[Template('purchasing/vendor_warranty_claim/close.html.twig')]
    public function changeStatus(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim, $status)
    {
        $client = $this->container->get(Client::class);
        $form = 'CLOSED' === mb_substr((string) $status, 0, 6) ? $this->createForm(VendorWarrantyClaimResolutionType::class)->handleRequest($request) : null;

        try {
            $apiStatus = $client->findOneBy(self::VENDOR_WARRANTY_CLAIM_STATUS_URL, ['name' => $status]);
        } catch (ClientException $e) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.errors.get_status', ['%status%' => $status], 'vendor_warranty_claim'));

            return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim['id']]);
        }

        $formSubmitted = false;
        $payload = ['status' => $apiStatus->getiri()];
        if (null !== $form && $form->isSubmitted() && $form->isValid()) {
            $payload = array_merge($payload, $form->getData());
            $formSubmitted = true;
        }

        if (!$form instanceof FormInterface || $formSubmitted) {
            $iri = $this->container->get(IriResolver::class)->resolve($vendorWarrantyClaim);

            try {
                $client->request($iri, $vendorWarrantyClaim->getIriId(), 'status', Request::METHOD_PUT, ['json' => $payload]);

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.success.status', [], 'vendor_warranty_claim'));
            } catch (ClientException $e) {
                $errorDescription = json_decode($e->getResponse()->getContent(false), true);
                $this->addFlash('error', \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('non_conformity.errors.status', [], 'non_conformity'), $errorDescription['hydra:description']));
            }

            return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => $vendorWarrantyClaim['id']]);
        }

        return [
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/main_file/{fileId}', requirements: ['id' => '\d+'], name: 'vendor_warranty_claim_mail_file_show', methods: 'GET')]
    public function showMainFile($id, $fileId)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/main_file/%s', self::WC_VENDOR_WARRANTY_CLAIM_URL, $id, $fileId));
    }

    #[Route(path: '/{vendorWarrantyClaimId}/files/{fileId}/change_visibility', name: 'vendor_warranty_file_change_visibility', methods: 'GET')]
    public function changeFileVisibility(#[ApiValueResolverAttribute(parameters: ['resource' => self::VENDOR_WARRANTY_CLAIM_FILE_URL, 'id' => 'fileId'])] ApiData $vendorWarrantyClaimFile, int $fileId, int $vendorWarrantyClaimId): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->put(
                \sprintf('files/%d', $fileId),
                ['json' => ['fileId' => $fileId, 'public' => true !== $vendorWarrantyClaimFile['public']]]
            );

            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('visibility.success', [], 'file_type'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash('error', \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('visibility.errors', [], 'file_type'), $errorDescription['hydra:description']));
        }

        return $this->redirectToRoute('vendor_warranty_claim_show_files', ['id' => $vendorWarrantyClaimId]);
    }

    #[Template('purchasing/vendor_warranty_claim/flop.html.twig')]
    #[Route(path: '/flop', name: 'vendor_warranty_claim_flop')]
    public function top10flopSuppliers(Request $request)
    {
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $locationParam = $request->query->get('location');

        $firstDayLastMonth = (new \DateTime('first day of last month'))->format('Y-m-d\TH:i:sP');
        $lastDayLastMonth = (new \DateTime('last day of last month'))->format('Y-m-d\TH:i:sP');

        $formData = [
            'createdAt' => [
                'from' => $firstDayLastMonth,
                'to' => $lastDayLastMonth,
            ],
            'status' => [
                '/purchasing/vendor_warranty_claim_statuses/1',
                '/purchasing/vendor_warranty_claim_statuses/2',
                '/purchasing/vendor_warranty_claim_statuses/3',
                '/purchasing/vendor_warranty_claim_statuses/4',
                '/purchasing/vendor_warranty_claim_statuses/5',
                '/purchasing/vendor_warranty_claim_statuses/6',
                '/purchasing/vendor_warranty_claim_statuses/7',
            ],
        ];

        if ($locationParam) {
            $formData['location'] = '/locations/'.$locationParam;
        }

        $form = $formFactory->create(
            VendorWarrantyClaimFlopType::class,
            $formData
        );

        $isFormSubmitted = false;
        $top10FlopSuppliers = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $isFormSubmitted = true;

                $options = [
                    'location' => $data['location'],
                    'createdAfter' => $data['createdAt']['from'],
                    'createdBefore' => $data['createdAt']['to'],
                    'status' => $data['status'],
                ];

                $report = $client->get('reports/resource=/purchasing/vendor_warranty_claims;x=top_ten_flop;y=supplier', ['query' => ['options' => $options]]);

                foreach ($report['yTotals'] as $key => $value) {
                    $top10FlopSuppliers[] = [
                        'supplierName' => explode(' - ', (string) $key)[0],
                        'supplierNumber' => explode(' - ', (string) $key)[1],
                        'supplierId' => explode(' - ', (string) $key)[2],
                        'value' => $value,
                    ];
                }

                usort($top10FlopSuppliers, static fn ($a, $b) => $b['value'] <=> $a['value']);
            } catch (ClientException $e) {
                $this->addFlash('error', $translator->trans('common.error.server'));
                $violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'formValues' => $form->getData(),
            'isFormSubmitted' => $isFormSubmitted,
            'data' => $top10FlopSuppliers,
        ];
    }

    private function createVWCFromForm(FormInterface $form, Request $request, string $iri)
    {
        $form->handleRequest($request);
        $vendorWarrantyClaim = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (null !== $data['supplier'] && '' !== $data['supplier']) {
                $data['supplierNumber'] = $data['supplier'];
                unset($data['supplier']);
            }

            try {
                $vendorWarrantyClaim = $this->container->get(Client::class)->save($iri, $data);

                if (isset($data['mainFile']) && null !== ($mainFile = $data['mainFile'])) {
                    $this->container->get(FileManager::class)->uploadFile(
                        $vendorWarrantyClaim,
                        $mainFile,
                        self::WC_VENDOR_WARRANTY_CLAIM_URL,
                        null,
                        'main_file',
                        false,
                        true
                    );
                }
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.success.creation', [], 'vendor_warranty_claim'));
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return $vendorWarrantyClaim;
    }
}
