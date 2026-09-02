<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\TechnicianOnCall;

use ApiBundle\Client;
use AppBundle\Filters\Type\Service\TechnicianOnCallKpiFilterType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Report\Service\CustomerServiceRecord\CustomerServiceRecordYearBacklog;
use AppBundle\Report\Service\TechnicianOnCall\NumberOpenAndClosedByMonth;
use AppBundle\Report\Service\TechnicianOnCall\TechnicianOnCallAverageTime;
use AppBundle\Report\Service\TechnicianOnCall\TechnicianOnCallBacklog;
use AppBundle\Report\Service\TechnicianOnCall\TechnicianOnCallImmediateRatio;
use AppBundle\Report\Service\TechnicianOnCall\TechnicianOnCallOldest;
use AppBundle\Report\Service\TechnicianOnCall\TechnicianOnCallOperate;
use AppBundle\Report\Service\TechnicianOnCall\TechnicianOnCallSparePartRequest;
use AppBundle\Report\Service\TechnicianOnCall\TechnicianOnCallTroubleshooting;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/service/technician-on-calls/kpi', defaults: ['alvest_module' => 'TOC', 'moduleDomain' => 'technician_on_calls'])]
class TechnicianOnCallKpiController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            NumberOpenAndClosedByMonth::class,
            TechnicianOnCallImmediateRatio::class,
            TechnicianOnCallAverageTime::class,
            TechnicianOnCallOldest::class,
            TechnicianOnCallBacklog::class,
            CustomerServiceRecordYearBacklog::class,
            Client::class,
            TechnicianOnCallOperate::class,
            TechnicianOnCallTroubleshooting::class,
            TechnicianOnCallSparePartRequest::class,
        ]);
    }

    #[Route(path: '', name: 'technician_on_calls_kpi', methods: ['GET'])]
    #[Template('service/technician_on_call/kpi.html.twig')]
    public function home(Request $request)
    {
        $defaults = [
            'from' => (new \DateTime('first day of -12 months 00:00:00'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
        ];

        $filterForm = $this->createForm(TechnicianOnCallKpiFilterType::class, $defaults, [
            'method' => 'GET',
            'enabledCustomerFilter' => true,
        ]);
        $filterForm->handleRequest($request);

        $parameters = $filterForm->getData();

        /** @var NumberOpenAndClosedByMonth $chartCreatedAndSoledByMonthBuilder */
        $chartCreatedAndSoledByMonthBuilder = $this->container->get(NumberOpenAndClosedByMonth::class);

        /** @var TechnicianOnCallImmediateRatio $chartImmediateRatioBuilder */
        $chartImmediateRatioBuilder = $this->container->get(TechnicianOnCallImmediateRatio::class);

        /** @var TechnicianOnCallAverageTime $chartAverageTimeBuilder */
        $chartAverageTimeBuilder = $this->container->get(TechnicianOnCallAverageTime::class);

        /** @var TechnicianOnCallOldest $chartOldestBuilder */
        $chartOldestBuilder = $this->container->get(TechnicianOnCallOldest::class);

        return [
            'filterForm' => $filterForm->createView(),
            'chartCreatedAndSoledByMonth' => $chartCreatedAndSoledByMonthBuilder->build($parameters),
            'chartImmediateRatio' => $chartImmediateRatioBuilder->build($parameters),
            'chartAverageTime' => $chartAverageTimeBuilder->build($parameters),
            'chartOldestBuilder' => $chartOldestBuilder->build($parameters),
        ];
    }

    #[Route(path: '/closure-time', name: 'technician_on_calls_closure_time', methods: ['GET'])]
    #[Template('service/technician_on_call/closure_time.html.twig')]
    public function closureTime(Request $request): array
    {
        $defaults = [
            'from' => (new \DateTime('first day of this months 00:00:00'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
        ];

        $filterForm = $this->createForm(TechnicianOnCallKpiFilterType::class, $defaults, [
            'method' => 'GET',
        ]);
        $filterForm->remove('to');
        $filterForm->handleRequest($request);

        $parameters = $filterForm->getData();
        unset($parameters['to']);

        /** @var Client $client */
        $client = $this->container->get(Client::class);

        $closureTimeReport = $client->get('/service/technician-on-call/closure_time_reports',
            ['query' => $parameters]
        );

        return [
            'filterForm' => $filterForm->createView(),
            'closureTimeReport' => $closureTimeReport['hydra:member'],
        ];
    }

    #[Route(path: '/backlog', name: 'technician_on_calls_backlog', methods: ['GET'])]
    #[Template('service/technician_on_call/backlog.html.twig')]
    public function backlog(Request $request): array
    {
        $defaults = [
            'from' => (new \DateTime('first day of -12 months 00:00:00'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
        ];

        $filterForm = $this->createForm(TechnicianOnCallKpiFilterType::class, $defaults, [
            'method' => 'GET',
        ]);
        $filterForm->handleRequest($request);

        $parameters = $filterForm->getData();

        /** @var TechnicianOnCallBacklog $chartTechnicianOnCallBuilder */
        $chartTechnicianOnCallBuilder = $this->container->get(TechnicianOnCallBacklog::class);

        /** @var CustomerServiceRecordYearBacklog $chartCustomerServiceRecordYearBacklog */
        $chartCustomerServiceRecordYearBacklog = $this->container->get(CustomerServiceRecordYearBacklog::class);

        return [
            'filterForm' => $filterForm->createView(),
            'chartTechnicianOnCallBuilder' => $chartTechnicianOnCallBuilder->build($parameters),
            'chartCustomerServiceRecordYearBacklog' => $chartCustomerServiceRecordYearBacklog->build($parameters),
        ];
    }

    #[Route(path: '/remote', name: 'technician_on_calls_remote', methods: ['GET'])]
    #[Template('service/technician_on_call/remote.html.twig')]
    public function remote(Request $request): array
    {
        $filterForm = $this->createForm(TechnicianOnCallKpiFilterType::class, [], [
            'method' => 'GET',
        ]);
        $filterForm->handleRequest($request);

        $parameters = $filterForm->getData();

        /** @var TechnicianOnCallOperate $chartTechnicianOnCallOperateBuilder */
        $chartTechnicianOnCallOperateBuilder = $this->container->get(TechnicianOnCallOperate::class);

        /** @var TechnicianOnCallTroubleshooting $chartTechnicianOnCallTroubleshootingBuilder */
        $chartTechnicianOnCallTroubleshootingBuilder = $this->container->get(TechnicianOnCallTroubleshooting::class);

        /** @var Client $client */
        $client = $this->container->get(Client::class);

        return [
            'filterForm' => $filterForm->createView(),
            'chartTechnicianOnCallOperateBuilder' => $chartTechnicianOnCallOperateBuilder->build($parameters),
            'chartTechnicianOnCallTroubleshootingBuilder' => $chartTechnicianOnCallTroubleshootingBuilder->build($parameters),
            'reportInterventionsByModel' => $client->get(
                '/reports/resource=/service/customer_service_records;x=equipmentRecord.model;y=salesOrganisationService.name',
                ['query' => ['options' => $parameters]]
            ),
        ];
    }

    #[Route(path: '/spare-part-request', name: 'technician_on_calls_spare_part_request', methods: ['GET'])]
    #[Template('service/technician_on_call/spare_part_request.html.twig')]
    public function sparePartRequest(Request $request): array
    {
        $filterForm = $this->createForm(TechnicianOnCallKpiFilterType::class, [], [
            'method' => 'GET',
        ]);
        $filterForm->handleRequest($request);

        $parameters = $filterForm->getData();

        /** @var TechnicianOnCallSparePartRequest $chartTechnicianOnCallSparePartRequestBuilder */
        $chartTechnicianOnCallSparePartRequestBuilder = $this->container->get(TechnicianOnCallSparePartRequest::class);

        /** @var Client $client */
        $client = $this->container->get(Client::class);

        return [
            'filterForm' => $filterForm->createView(),
            'chartTechnicianOnCallSparePartRequestBuilder' => $chartTechnicianOnCallSparePartRequestBuilder->build($parameters),
            'reportSparePartsRequestDispatchTime' => $client->get(
                '/reports/resource=/parts/spare_parts_requests;x=delay;y=salesOrganisationService.name',
                ['query' => ['options' => $parameters]]
            ),
        ];
    }

    #[Route(path: '/factory-flag', name: 'technician_on_calls_factory_flag', methods: ['GET'])]
    #[Template('service/technician_on_call/factory_flag.html.twig')]
    public function factoryFlag(Request $request): array
    {
        $filterForm = $this->createForm(TechnicianOnCallKpiFilterType::class, [], [
            'method' => 'GET',
        ]);
        $filterForm->handleRequest($request);

        $parameters = $filterForm->getData();

        /** @var Client $client */
        $client = $this->container->get(Client::class);

        return [
            'filterForm' => $filterForm->createView(),
            'reportFactoryFlagByModel' => $client->get(
                '/reports/resource=/service/technician_on_calls;x=equipmentRecord.model;y=factoryFlag',
                ['query' => ['options' => $parameters]]
            ),
        ];
    }
}
