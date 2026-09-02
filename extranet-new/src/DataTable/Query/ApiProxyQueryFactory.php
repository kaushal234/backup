<?php

declare(strict_types=1);

namespace App\DataTable\Query;

use App\CQRS\Query\QueryInterface;
use App\CQRS\QueryBusInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryFactoryInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'kreyu_data_table.proxy_query.factory')]
readonly class ApiProxyQueryFactory implements ProxyQueryFactoryInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function create(mixed $data): ProxyQueryInterface
    {
        return new ApiProxyQuery($data, $this->queryBus);
    }

    public function supports(mixed $data): bool
    {
        return $data instanceof QueryInterface;
    }
}
