<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\TechnicianOnCall;

use ApiBundle\Client;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/service/technician-on-calls', defaults: ['alvest_module' => 'TOC', 'moduleDomain' => 'technician_on_calls'])]
class TechnicianOnCallReportsController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
        ]);
    }

    #[Route(path: '/reports', name: 'technician_on_calls_reports', methods: ['GET'])]
    #[Template('service/technician_on_call/reports.html.twig')]
    public function home()
    {
        /** @var Client $client */
        $client = $this->container->get(Client::class);

        return [
            'reportByIndiceFactor' => $client->get('reports/resource=/service/technician_on_calls;x=salesOrganisationService.name;y=indiceFactor'),
            'reportByStatus' => $client->get('reports/resource=/service/technician_on_calls;x=salesOrganisationService.name;y=status'),
            'reportByFactory' => $client->get('reports/resource=/service/technician_on_calls;x=equipmentRecord.manufacturerLocation.name;y=salesOrganisationService.name'),
            'reportSurvey' => $client->get('reports/reports/resource=/service/technician_on_call_surveys;x=salesOrganisationService.name;y=quantity'),
        ];
    }
}
