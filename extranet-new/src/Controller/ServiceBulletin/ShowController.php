<?php

declare(strict_types=1);

namespace App\Controller\ServiceBulletin;

use App\CQRS\Query\Service\FindAllServiceBulletinFileQuery;
use App\CQRS\Query\Service\FindServiceBulletinQuery;
use App\CQRS\QueryBusInterface;
use App\DataTable\Type\Service\ServiceBulletinEquipmentDataTableType;
use App\Http\Responder;
use App\Sdk\Resource\Customer;
use App\Sdk\Resource\ServiceBulletin;
use App\Sdk\Resource\ServiceBulletinFile;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/service_bulletin')]
class ShowController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/{id}', name: 'service_bulletin:show', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request, int $id): Response
    {
        $activeCustomer = $request->getSession()->get('customer');
        $customerLegacyId = $activeCustomer instanceof Customer ? $activeCustomer->legacyId : null;

        /** @var ServiceBulletin $serviceBulletin */
        $serviceBulletin = $this->queryBus->dispatch(new FindServiceBulletinQuery($id, $customerLegacyId));
        /** @var array<ServiceBulletinFile> $serviceBulletinFiles */
        $serviceBulletinFiles = $this->queryBus->dispatch(new FindAllServiceBulletinFileQuery($id));

        $equipmentDataTable = $this->createDataTable(ServiceBulletinEquipmentDataTableType::class, $serviceBulletin->equipments);
        $equipmentDataTable->handleRequest($request);

        if ($equipmentDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($equipmentDataTable);
        }

        return $this->responder->render('service/show.html.twig',
            [
                'serviceBulletin' => $serviceBulletin,
                'serviceBulletinFiles' => $serviceBulletinFiles,
                'equipmentDataTable' => $equipmentDataTable->createView(),
            ]
        );
    }
}
