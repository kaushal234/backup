<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\ServiceArea;

use ApiBundle\Hydra\HydraCollection;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Type\Service\AirportDataTableType;
use AppBundle\DataTable\Type\Service\ServiceAreaDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/service-areas', defaults: ['breadcrumb_label' => 'menu.service_areas.title', 'moduleDomain' => 'service_area'])]
class IndexController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public const string RESOURCE = 'service_areas';
    private const string AIRPORTS_RESOURCE_URL = 'airports';

    #[Route(path: '', name: 'service_area_home', methods: ['GET'])]
    #[Template('service/service_area/index.html.twig')]
    public function __invoke(Request $request, #[ApiValueResolverAttribute] HydraCollection $serviceAreas)
    {
        $datatable = $this->createDataTable(ServiceAreaDataTableType::class, self::RESOURCE);
        $datatable->handleRequest($request);

        $unassignedAirportsDatatable = $this->createDataTable(
            AirportDataTableType::class,
            self::AIRPORTS_RESOURCE_URL.'?normalization_groups[]=airport_service_areas'
        );
        $unassignedAirportsDatatable->handleRequest($request);

        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        if ($unassignedAirportsDatatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($unassignedAirportsDatatable);
        }

        return [
            'serviceAreaDatatable' => $datatable->createView(),
            'unassignedAirportsDatatable' => $unassignedAirportsDatatable->createView(),
        ];
    }
}
