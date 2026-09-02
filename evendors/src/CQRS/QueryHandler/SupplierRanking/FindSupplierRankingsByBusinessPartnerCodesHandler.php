<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\SupplierRanking;

use App\CQRS\Query\SupplierRanking\FindSupplierRankingsByBusinessPartnerCodesQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\SupplierRanking\SupplierRanking;
use Psl\Collection\AccessibleCollectionInterface;

final class FindSupplierRankingsByBusinessPartnerCodesHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<SupplierRanking>
     */
    public function __invoke(FindSupplierRankingsByBusinessPartnerCodesQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(SupplierRanking::class);
    }
}
