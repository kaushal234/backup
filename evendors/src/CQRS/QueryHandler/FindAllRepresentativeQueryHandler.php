<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler;

use App\CQRS\Query\FindAllRepresentativeQuery;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\Representative;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllRepresentativeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, Representative>
     */
    public function __invoke(FindAllRepresentativeQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Representative::class, ['disabled' => false]);
    }
}
