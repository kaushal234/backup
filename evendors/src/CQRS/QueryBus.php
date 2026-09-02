<?php

declare(strict_types=1);

namespace App\CQRS;

use App\CQRS\Query\QueryInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class QueryBus implements QueryBusInterface
{
    use HandleTrait {
        handle as handleQuery;
    }

    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    /**
     * @template T
     *
     * @param QueryInterface<T> $query
     *
     * @return T
     */
    public function dispatch(QueryInterface $query): mixed
    {
        return $this->handleQuery($query);
    }
}
