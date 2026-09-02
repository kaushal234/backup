<?php

declare(strict_types=1);

namespace App\Controller\Catalogue;

use App\Controller\AbstractAutocompleteController;
use App\CQRS\Query\Catalogue\FindPaginateProductsQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/catalogue/product/autocomplete', name: 'autocomplete_product')]
final class ProductAutocompleteController extends AbstractAutocompleteController
{
    protected function configureOptions(OptionsResolver $resolver, Request $request): void
    {
        $resolver->setDefaults([
            'routeName' => 'autocomplete_product',
            'queryBus' => static function ($page, $options) {
                return new FindPaginateProductsQuery(
                    page: $page,
                    itemsPerPage: 25,
                    options: $options
                );
            },
        ]);
    }
}
