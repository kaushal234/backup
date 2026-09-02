<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindAllEquipmentRecordsPublicQuery;
use App\DataTable\Type\Equipment\EquipmentRecordPublicDataTableType;
use App\Http\Responder;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class IndexPublicController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    // Higher priority than the catch-all `/public/{serialNumber}` route so `/public/equipments`
    // is not captured as a serial number.
    #[Route(path: '/public/equipments', name: 'equipment:index:public', methods: [Request::METHOD_GET], priority: 10)]
    public function __invoke(Request $request): Response
    {
        $dataTable = $this->createDataTable(
            EquipmentRecordPublicDataTableType::class,
            new FindAllEquipmentRecordsPublicQuery()
        );

        $dataTable->handleRequest($request);
        if ($dataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($dataTable);
        }

        return $this->responder->render('equipment_records/index_public.html.twig', [
            'datatable' => $dataTable->createView(),
        ]);
    }
}
