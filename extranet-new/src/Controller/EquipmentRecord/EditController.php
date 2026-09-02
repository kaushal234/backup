<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindEquipmentRecordQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\TechnicianOnCall\Airport;
use App\DataTransferObject\UpdateEquipmentRecord;
use App\Form\Type\EquipmentType;
use App\Http\Responder;
use App\Sdk\Resource\EquipmentRecord;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsController]
#[Route(path: '/equipments')]
class EditController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(path: '/{id}/edit', name: 'equipment:edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(int $id, Request $request): Response
    {
        /** @var EquipmentRecord $equipment */
        $equipment = $this->queryBus->dispatch(new FindEquipmentRecordQuery($id));
        $data = new UpdateEquipmentRecord();

        $airport = new Airport();
        $airport->iri = $equipment->airport->iri;

        $data->iri = $equipment->iri;
        $data->airport = $airport;
        $data->customerSerialNumber = $equipment->customerSerialNumber;

        $form = $this->formFactory->create(EquipmentType::class, $data, [
            'action' => $this->urlGenerator->generate('equipment:update', ['id' => $equipment->id]),
        ]);

        return $this->responder->render('equipment_records/update.html.twig', [
            'equipment' => $equipment,
            'form' => $form->createView(),
        ]);
    }
}
