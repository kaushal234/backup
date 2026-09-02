<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Handler;

use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

class ChangeLogTypeFilterHandler implements FilterHandlerInterface
{
    private const REVERSE_MAPPING = [
        'Internal' => ['build', 'ci', 'docs', 'refactor', 'test', 'chore', 'revert'],
        'New' => ['feat'],
        'Fix' => ['fix'],
        'Performance' => ['perf'],
        'UI' => ['style'],
    ];

    public function handle(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter): void
    {
        if (!$query instanceof ApiProxyQuery) {
            throw new UnexpectedTypeException($query, ApiProxyQuery::class);
        }

        $value = $data->getValue();

        if (empty($value)) {
            return;
        }

        $expanded = [];
        foreach ((array) $value as $displayLabel) {
            $keys = self::REVERSE_MAPPING[$displayLabel] ?? [mb_strtolower((string) $displayLabel)];
            array_push($expanded, ...$keys);
        }

        $query->setFilter('type', array_unique($expanded));
    }
}
