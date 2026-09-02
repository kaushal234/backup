<?php

declare(strict_types=1);

namespace App\CQRS;

use App\CQRS\Query\QueryInterface;

interface QueryBusInterface
{
    /**
     * @template T
     *
     * @param QueryInterface<T> $query
     *
     * @return T
     */
    public function dispatch(QueryInterface $query): mixed;
}
