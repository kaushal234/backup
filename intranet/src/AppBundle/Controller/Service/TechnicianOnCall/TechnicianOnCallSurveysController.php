<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\TechnicianOnCall;

use AppBundle\DataTable\Type\Service\TechnicianOnCallSurveysDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/service/technician-on-calls', defaults: ['alvest_module' => 'TOC', 'moduleDomain' => 'technician_on_calls'])]
class TechnicianOnCallSurveysController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    #[Route(path: '/surveys', name: 'technician_on_call_surveys', methods: ['GET', 'POST'])]
    #[Template('service/technician_on_call/surveys.html.twig')]
    public function home(Request $request)
    {
        $datatable = $this->createDataTable(TechnicianOnCallSurveysDataTableType::class, TechnicianOnCallSurveysDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        return [
            'technicianOnCallSurveysDatatable' => $datatable->createView(),
        ];
    }
}
