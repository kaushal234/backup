<?php

declare(strict_types=1);

namespace App\Controller;

use App\CQRS\Query\FindPaginateCountriesQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/countries/autocomplete', name: 'autocomplete_countries')]
final class CountryAutocompleteController extends AbstractAutocompleteController
{
    protected function configureOptions(OptionsResolver $resolver, Request $request): void
    {
        $resolver->setDefaults([
            'routeName' => 'autocomplete_countries',
            'queryBus' => static function ($page, $options) {
                return new FindPaginateCountriesQuery(
                    page: $page,
                    itemsPerPage: 25,
                    options: $options
                );
            },
        ]);
    }
}
