<?php

declare(strict_types=1);

namespace App\CQRS\Query\Manufacturing;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\Manufacturing\BillOfMaterials;

/**
 * @implements QueryInterface<BillOfMaterials>
 */
final class FindBillOfMaterialQuery implements QueryInterface
{
    public function __construct(
        public readonly string $item,
        public readonly ?int $site = null,
        public readonly ?string $effectiveDate = null,
    ) {
    }
}
