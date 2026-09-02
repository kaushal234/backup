<?php

declare(strict_types=1);

namespace App\CQRS\Query\SupplierRanking;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\SupplierRanking\SupplierRanking;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<SupplierRanking>>
 */
final class FindSupplierRankingsByBusinessPartnerCodesQuery implements QueryInterface
{
}
