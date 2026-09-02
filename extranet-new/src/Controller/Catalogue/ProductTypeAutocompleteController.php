<?php

declare(strict_types=1);

namespace App\Controller\Catalogue;

use App\CQRS\Query\Catalogue\FindAllProductTypesQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Resource\ProductType;
use App\Sdk\Utils\IriToId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class ProductTypeAutocompleteController
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/catalogue/product-type/autocomplete', name: 'autocomplete_product_type')]
    public function __invoke(Request $request): Response
    {
        $term = (string) $request->query->get('query', '');

        if (mb_strlen($term) < 2) {
            return new JsonResponse([]);
        }

        $productTypes = $this->queryBus->dispatch(new FindAllProductTypesQuery(options: ['autocomplete' => $term]));

        return new JsonResponse([
            'results' => array_map(static fn (ProductType $productType) => [
                'value' => IriToId::iriToId($productType->iri),
                'text' => $productType->englishName,
            ], $productTypes->toArray()),
        ]);
    }
}
