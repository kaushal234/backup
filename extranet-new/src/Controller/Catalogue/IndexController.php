<?php

declare(strict_types=1);

namespace App\Controller\Catalogue;

use App\CQRS\Query\Catalogue\FindAllProductTypesQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/catalogue')]
class IndexController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '', name: 'catalogue:index', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        $productTypes = $this->queryBus->dispatch(new FindAllProductTypesQuery());

        return $this->responder->render('catalogue/product_types.html.twig', ['productTypes' => $productTypes]);
    }
}
