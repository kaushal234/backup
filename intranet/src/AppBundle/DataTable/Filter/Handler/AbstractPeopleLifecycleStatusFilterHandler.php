<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Handler;

use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

/**
 * Maps a multi-select people lifecycle status to the ApiPlatform `disabled`
 * BooleanFilter on the People resource. When every status (or none) is
 * selected, no filter is applied so the full result set is returned.
 */
abstract class AbstractPeopleLifecycleStatusFilterHandler implements FilterHandlerInterface
{
    public function handle(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter): void
    {
        if (!$query instanceof ApiProxyQuery) {
            throw new UnexpectedTypeException($query, ApiProxyQuery::class);
        }

        $map = $this->getStatusToDisabledMap();
        $values = array_values(array_unique((array) $data->getValue()));
        $selected = array_values(array_intersect(array_keys($map), $values));

        // Only a single status narrows the result set; none or all means no filter.
        if (1 !== \count($selected)) {
            return;
        }

        $query->setFilter('disabled', $map[$selected[0]]);
    }

    /**
     * Maps each selectable status to the `disabled` filter value it implies.
     *
     * @return array<string, string> status value => 'true'|'false'
     */
    abstract protected function getStatusToDisabledMap(): array;
}
