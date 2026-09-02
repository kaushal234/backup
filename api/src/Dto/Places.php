<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\DataProvider\PlaceDataProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/places/{iri}',
            requirements: ['iri' => '.+'],
            openapi: new Operation(
                summary: 'Get all status for a resource from his collection IRI',
                parameters: [
                    new Parameter(
                        name: 'iri',
                        in: 'path',
                        description: 'IRI of the resource collection',
                        required: true,
                        schema: ['type' => 'string'],
                        example: '/places/mis/trouble_ticket',
                    ),
                ],
            ),
            provider: PlaceDataProvider::class,
        ),
    ],
)]
class Places
{
    public array $places;
}
