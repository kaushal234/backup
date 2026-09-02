<?php

declare(strict_types=1);

namespace App\AI\Dto;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\AI\DataProvider\Search\SearchDataProvider;
use App\Filter\AI\AISearchFilter;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/ai/search/intranet',
            openapi: true,
            provider: SearchDataProvider::class
        ),
    ],
)]
#[ApiFilter(AISearchFilter::class)]
final class IntranetSearch extends AbstractQuerySearch
{
}
