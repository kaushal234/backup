<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Handler;

use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

class ApiExistFilterHandler implements FilterHandlerInterface
{
    public function handle(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter): void
    {
        if (!$query instanceof ApiProxyQuery) {
            throw new UnexpectedTypeException($query, ApiProxyQuery::class);
        }

        $query->existsFilter($filter, $data);
    }
}
