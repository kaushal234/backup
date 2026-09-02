<?php

declare(strict_types=1);

namespace App\Controller\KPI;

use App\Chart\ChartBuilderFactory;
use App\CQRS\Query\KPI\FindReportQuery;
use App\CQRS\Query\VendorWarrantyClaim\FindAllVendorWarrantyClaimsQuery;
use App\CQRS\QueryBusInterface;
use App\Filter\Type\ReportFilterType;
use App\Http\Responder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use function count;

#[Route('/kpi')]
final class IndexController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $bus,
        private readonly TranslatorInterface $translator,
        private readonly ChartBuilderFactory $chartBuilderFactory,
        private readonly FormFactoryInterface $factory,
    ) {
    }

    #[Route('', name: 'kpi:index', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $formFilter = $this->factory->create(ReportFilterType::class);
        $formFilter->handleRequest($request);

        $topPartFailure = null;
        $reportPartFailureHistory = null;
        $reportSupplierHistory = null;
        $reportSupplierHistorySupplierCorrectiveActionRequest = null;
        $chartRecoveryCost = null;
        $vendorWarrantyClaims = [];
        $filtered = false;

        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $filters = $formFilter->getData();
            $filtered = true;
            if ('ALL' === $filters['factory']) {
                unset($filters['factory']);
            }

            $options = [
                'rejected' => 0,
                ...$filters,
            ];

            $reportPartFailure = $this->bus->dispatch(new FindReportQuery(resource: '/purchasing/vendor_warranty_claims', x: 'top_part_failure', options: $options));
            $reportPartFailureHistory = $this->bus->dispatch(new FindReportQuery(resource: '/purchasing/vendor_warranty_claims', x: 'part_failure_history', y: 'supplier', options: $options));
            $reportPartFailureHistory->sortRowsByTotal('DESC');
            $reportSupplierHistory = $this->bus->dispatch(new FindReportQuery(resource: '/purchasing/vendor_warranty_claims', x: 'supplier_history', options: $options));
            $reportSupplierHistorySupplierCorrectiveActionRequest = $this->bus->dispatch(new FindReportQuery(resource: '/quality/supplier_corrective_action_requests', x: 'supplier_history', options: $options));
            $reportRecoveryCost = $this->bus->dispatch(new FindReportQuery(resource: '/purchasing/vendor_warranty_claims', x: 'supplier_recovery_cost', options: $options));
            $vendorWarrantyClaims = $this->bus->dispatch(new FindAllVendorWarrantyClaimsQuery(
                [
                    'status.name' => 'VENDOR_TO_RESPOND',
                    ...$filters,
                    'order' => ['requestedCreditAmount' => 'DESC'],
                ]
            ));
            $chartRecoveryCost = 0 === count($reportRecoveryCost->rows) ? null : $this->chartBuilderFactory
                ->getColumnChartBuilder()
                ->addYAxis('Amount')
                ->setTitle($this->translator->trans('vendor_warranty_claim.report.recovery_cost', [], 'vendor_warranty_claim'))
            ;

            foreach ($reportRecoveryCost->rows as $key => $data) {
                foreach ($data as $credit => $value) {
                    $chartRecoveryCost->addPlot($credit, $key, $value['value']);
                }
            }

            $topPartFailure = [];
            foreach ($reportPartFailure->yTotals as $key => $value) {
                $topPartFailure[] = [
                    'name' => $key,
                    'value' => $value,
                ];
            }
        }

        return $this->responder->render('kpi/index.html.twig', [
            'vendorWarrantyClaims' => $vendorWarrantyClaims,
            'topPartFailureReport' => $topPartFailure,
            'reportPartFailureHistory' => $reportPartFailureHistory,
            'reportSupplierHistory' => $reportSupplierHistory,
            'reportSupplierHistorySupplierCorrectiveActionRequest' => $reportSupplierHistorySupplierCorrectiveActionRequest,
            'chartRecoveryCost' => $chartRecoveryCost?->buildConfig() ?? null,
            'formFilter' => $formFilter->createView(),
            'filtered' => $filtered,
        ]);
    }
}
