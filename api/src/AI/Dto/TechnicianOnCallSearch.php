<?php

declare(strict_types=1);

namespace App\AI\Dto;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\AI\DataProvider\Search\SearchDataProvider;
use App\Filter\AI\AISearchFilter;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/ai/search/toc',
            openapi: new Operation(
                summary: 'Use to search information into TOC module.',
                parameters: [
                    new Parameter(
                        name: 'query',
                        in: 'query',
                        required: true,
                    ),
                ],
            ),
            provider: SearchDataProvider::class,
        ),
    ],
)]
#[ApiFilter(AISearchFilter::class)]
final class TechnicianOnCallSearch extends AbstractQuerySearch
{
}
