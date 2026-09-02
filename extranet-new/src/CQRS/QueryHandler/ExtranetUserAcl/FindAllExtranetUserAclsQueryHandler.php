<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\ExtranetUserAcl;

use App\CQRS\Query\ExtranetUserAcl\FindAllExtranetUserAclsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ExtranetUserAcl;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllExtranetUserAclsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindAllExtranetUserAclsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(ExtranetUserAcl::class, [
            ...$query->options,
            'query' => [
                'normalizationGroups' => ['location', 'location_address', 'address', 'people:business_unit', 'business_unit_public', 'file:light', 'people_photo', 'expose_legacy'],
            ],
        ]);
    }
}
