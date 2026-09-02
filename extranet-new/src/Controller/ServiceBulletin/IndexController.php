<?php

declare(strict_types=1);

namespace App\Controller\ServiceBulletin;

use App\CQRS\Query\Service\FindAllServiceBulletinsQuery;
use App\DataTable\Type\Service\ServiceBulletinDataTableType;
use App\Http\Responder;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/services/service-bulletins')]
class IndexController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    #[Route(path: '', name: 'page:service_bulletins', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $query = new FindAllServiceBulletinsQuery();
        $datatable = $this->createDataTable(ServiceBulletinDataTableType::class, $query);

        $datatable->handleRequest($request);
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return $this->responder->render('service/index.html.twig',
            [
                'datatable' => $datatable->createView(),
            ]
        );
    }
}
