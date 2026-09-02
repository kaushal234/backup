<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindAllEquipmentRecordFilesQuery;
use App\CQRS\Query\EquipmentRecord\FindAllEquipmentRecordsQuery;
use App\CQRS\Query\EquipmentRecord\FindEquipmentRecordQuery;
use App\CQRS\Query\EquipmentSerial\FindAllEquipmentSerialsQuery;
use App\CQRS\Query\Service\FindAllServiceBulletinsQuery;
use App\CQRS\Query\WarrantyClaim\FindAllWarrantyClaimsQuery;
use App\CQRS\QueryBusInterface;
use App\DataTable\Type\Service\ServiceBulletinDataTableType;
use App\DataTable\Type\WarrantyClaim\WarrantyClaimDataTableType;
use App\Http\Responder;
use App\Sdk\Resource\EquipmentRecord;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/equipments')]
class ShowController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/{id}', name: 'equipment:show', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[Route(path: '/serial_number/{serialNumber}', name: 'equipment:show_by_serial_number', requirements: ['serialNumber' => '.+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request, ?int $id = null, ?string $serialNumber = null): Response
    {
        if (null !== $serialNumber) {
            $equipments = $this->queryBus->dispatch(new FindAllEquipmentRecordsQuery(page: 1, itemsPerPage: 1, options: ['serialNumber' => $serialNumber, 'normalization_groups' => ['iata_code_detail']]));

            /** @var EquipmentRecord|null $equipment */
            $equipment = $equipments->items->first();

            if (null === $equipment) {
                $this->addFlash('danger', $this->translator->trans('extranet.error.no_equipment_record_found', [], 'messages').$serialNumber);

                return $this->redirectToRoute('equipment:index');
            }

            $id = $equipment->id;
        }

        /** @var EquipmentRecord $equipment */
        $equipment = $this->queryBus->dispatch(new FindEquipmentRecordQuery($id));
        $serviceBulletinsByEquipment = new FindAllServiceBulletinsQuery(options: ['lines.equipmentRecord.id' => $equipment->legacyId]);
        $datatable = $this->createDataTable(ServiceBulletinDataTableType::class, $serviceBulletinsByEquipment);
        $datatable->handleRequest($request);

        $warrantyClaimsByEquipment = new FindAllWarrantyClaimsQuery(options: ['serialNumber' => $equipment->serialNumber]);
        $warrantyClaimDatatable = $this->createDataTable(WarrantyClaimDataTableType::class, $warrantyClaimsByEquipment);
        $warrantyClaimDatatable->handleRequest($request);

        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }
        if ($warrantyClaimDatatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($warrantyClaimDatatable);
        }
        $equipmentSerialsSchematics = $this->queryBus->dispatch(new FindAllEquipmentSerialsQuery(equipmentRecordSerialNumber: $equipment->serialNumber));
        $customerFiles = $this->queryBus->dispatch(new FindAllEquipmentRecordFilesQuery($equipment->legacyId));

        return $this->responder->render('equipment_records/show.html.twig',
            [
                'equipment' => $equipment,
                'datatable' => $datatable->createView(),
                'warrantyClaimDatatable' => $warrantyClaimDatatable->createView(),
                'equipmentSerialsSchematics' => $equipmentSerialsSchematics,
                'customerFiles' => $customerFiles,
            ]
        );
    }
}
