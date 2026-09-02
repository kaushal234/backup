<?php

declare(strict_types=1);

namespace App\Controller\Catalogue;

use App\CQRS\Query\Catalogue\FindProductTypeQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\ProductType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/catalogue')]
class ShowProductFamiliesController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/{id}/product-families', name: 'catalogue:product_families', methods: [Request::METHOD_GET])]
    public function __invoke(int $id): Response
    {
        /** @var ProductType $productType */
        $productType = $this->queryBus->dispatch(new FindProductTypeQuery(id: $id));

        return $this->responder->render('catalogue/product_families.html.twig', ['productType' => $productType]);
    }
}
