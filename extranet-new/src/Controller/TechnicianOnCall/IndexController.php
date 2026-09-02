<?php

declare(strict_types=1);

namespace App\Controller\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllTechnicianOnCallQuery;
use App\DataTable\Query\ApiProxyQuery;
use App\DataTable\Type\Service\TechnicianOnCallDataTableType;
use App\Http\Responder;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/technician-on-calls')]
class IndexController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    #[Route(path: '', name: 'technician_on_call:index', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $query = new FindAllTechnicianOnCallQuery();
        $datatable = $this->createDataTable(TechnicianOnCallDataTableType::class, $query);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return $this->responder->render('technician_on_call/index.html.twig', [
            'datatable' => $datatable->createView(),
        ]);
    }
}
