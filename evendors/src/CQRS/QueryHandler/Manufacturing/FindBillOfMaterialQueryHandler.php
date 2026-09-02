<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Manufacturing;

use App\CQRS\Query\Manufacturing\FindBillOfMaterialQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\Manufacturing\BillOfMaterials;

final class FindBillOfMaterialQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(FindBillOfMaterialQuery $query): BillOfMaterials
    {
        return $this->client->find(BillOfMaterials::class, [
            'site' => $query->site,
            'item' => $query->item,
            'effectiveDate' => $query->effectiveDate,
        ]);
    }
}
