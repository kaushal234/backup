<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindAllEquipmentRecordsQuery;
use App\DataTable\Type\Equipment\EquipmentRecordDataTableType;
use App\Http\Responder;
use App\Sdk\Resource\Customer;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/equipments')]
class IndexController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    #[Route(path: '', name: 'equipment:index', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        /** @var Customer $activeCustomer */
        $activeCustomer = $request->getSession()->get('customer');
        $query = new FindAllEquipmentRecordsQuery(options: ['by_customer' => $activeCustomer->getIri(), 'normalization_groups' => ['iata_code_detail']]);
        $datatable = $this->createDataTable(EquipmentRecordDataTableType::class, $query);
        $datatable->handleRequest($request);
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return $this->responder->render('equipment_records/index.html.twig',
            [
                'datatable' => $datatable->createView(),
            ]
        );
    }
}
