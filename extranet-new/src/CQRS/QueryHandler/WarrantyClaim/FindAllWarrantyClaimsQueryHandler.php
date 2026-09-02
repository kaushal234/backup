<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\WarrantyClaim;

use App\CQRS\Query\WarrantyClaim\FindAllWarrantyClaimsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Page;
use App\Sdk\Resource\WarrantyClaim;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllWarrantyClaimsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllWarrantyClaimsQuery $query): Page
    {
        return $this->client->paginate(
            resource: WarrantyClaim::class,
            page: $query->page,
            itemsPerPage: $query->itemsPerPage,
            criteria: [
                ...$query->options,
            ]
        );
    }
}
