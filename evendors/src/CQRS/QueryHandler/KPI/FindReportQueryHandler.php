<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\KPI;

use App\CQRS\Query\KPI\FindReportQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\Report;

final class FindReportQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(FindReportQuery $query): Report
    {
        return $this->client->find(Report::class, [
            'resource' => $query->resource,
            'x' => $query->x,
            'y' => $query->y,
            'options' => $query->options,
        ]);
    }
}
