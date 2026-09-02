<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindEquipmentRecordQuery;
use App\CQRS\Query\EquipmentSerial\FindAllEquipmentSerialsQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\EquipmentRecord;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/equipments')]
class SerialsController extends AbstractController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/{id}/serials', name: 'equipment:serials', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function __invoke(int $id): Response
    {
        /** @var EquipmentRecord $equipment */
        $equipment = $this->queryBus->dispatch(new FindEquipmentRecordQuery($id));

        $equipmentSerials = $this->queryBus->dispatch(new FindAllEquipmentSerialsQuery(equipmentRecordSerialNumber: $equipment->serialNumber, schematics: false));

        return $this->responder->render('equipment_records/serials.html.twig',
            [
                'equipment' => $equipment,
                'equipmentSerials' => $equipmentSerials,
            ]
        );
    }
}
