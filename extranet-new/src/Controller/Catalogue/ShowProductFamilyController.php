<?php

declare(strict_types=1);

namespace App\Controller\Catalogue;

use App\CQRS\Query\Catalogue\FindAllDatasheetsQuery;
use App\CQRS\Query\Catalogue\FindProductFamilyQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\ProductFamily;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/catalogue')]
class ShowProductFamilyController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/product-families/{id}', name: 'catalogue:product_family', methods: [Request::METHOD_GET])]
    public function __invoke(int $id): Response
    {
        /** @var ProductFamily $productFamily */
        $productFamily = $this->queryBus->dispatch(new FindProductFamilyQuery(id: $id));

        $datasheets = $this->queryBus->dispatch(new FindAllDatasheetsQuery(productFamilyIri: $productFamily->getIri()));

        return $this->responder->render('catalogue/product_family.html.twig', ['productFamily' => $productFamily, 'datasheets' => $datasheets]);
    }
}
