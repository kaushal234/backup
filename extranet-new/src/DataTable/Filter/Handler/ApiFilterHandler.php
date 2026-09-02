<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Handler;

use App\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

class ApiFilterHandler implements FilterHandlerInterface
{
    public function handle(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter): void
    {
        if (!$query instanceof ApiProxyQuery) {
            throw new UnexpectedTypeException($query, ApiProxyQuery::class);
        }

        $value = $data->getValue();

        if (null === $value) {
            return;
        }

        $extractor = $filter->getConfig()->getOption('value_extractor');
        if (null !== $extractor) {
            if (!is_iterable($value)) {
                $value = $extractor($value);
            } else {
                $extractedValue = [];
                foreach ($value as $item) {
                    $extractedValue[] = $extractor($item);
                }

                $value = $extractedValue;
            }
        }

        $query->filter($filter, new FilterData($value));
    }
}
