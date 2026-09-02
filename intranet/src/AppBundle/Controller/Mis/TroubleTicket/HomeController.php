<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Mis\TroubleTicketDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class HomeController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    #[Route(name: 'trouble_ticket_home', methods: ['GET|POST'])]
    #[Template('mis/trouble_ticket/home.html.twig')]
    public function __invoke(Request $request)
    {
        $datatable = $this->createDataTable(TroubleTicketDataTableType::class, TroubleTicketDataTableType::RESOURCE);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return [
            'troubleTicketDatable' => $datatable->createView(),
        ];
    }
}
