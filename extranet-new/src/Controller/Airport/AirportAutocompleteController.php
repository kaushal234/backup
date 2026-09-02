<?php

declare(strict_types=1);

namespace App\Controller\Airport;

use App\Controller\AbstractAutocompleteController;
use App\CQRS\Query\Airport\FindPaginateAirportsQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/airports/autocomplete', name: 'autocomplete_airports')]
final class AirportAutocompleteController extends AbstractAutocompleteController
{
    protected function configureOptions(OptionsResolver $resolver, Request $request): void
    {
        $resolver->setDefaults([
            'routeName' => 'autocomplete_airports',
            'orderProperty' => 'code',
            'formatter' => static fn ($item) => \sprintf('%s - %s', $item->code, $item->city),
            'queryBus' => static function ($page, $options) {
                return new FindPaginateAirportsQuery(
                    page: $page,
                    itemsPerPage: 25,
                    options: $options
                );
            },
        ]);
    }
}
