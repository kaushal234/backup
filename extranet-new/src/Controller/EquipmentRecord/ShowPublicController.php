<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindEquipmentRecordPublicQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\EquipmentRecordPublic;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/public')]
class ShowPublicController
{
    public const string PUBLIC_SEARCH_EQUIPMENT = 'equipment:show:public';

    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/{serialNumber}', name: self::PUBLIC_SEARCH_EQUIPMENT, methods: [Request::METHOD_GET])]
    public function __invoke(string $serialNumber): Response
    {
        try {
            /** @var EquipmentRecordPublic|null $equipment */
            $equipment = $this->queryBus->dispatch(
                new FindEquipmentRecordPublicQuery($serialNumber)
            );
        } catch (\Throwable) {
            $equipment = null;
        }

        return $this->responder->render('equipment_records/show_public.html.twig', [
            'equipment' => $equipment,
            'serialNumber' => $serialNumber,
        ]);
    }
}
