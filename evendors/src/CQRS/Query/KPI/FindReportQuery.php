<?php

declare(strict_types=1);

namespace App\CQRS\Query\KPI;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\Report;

/**
 * @implements QueryInterface<Report>
 */
final class FindReportQuery implements QueryInterface
{
    /**
     * @param array<string> $options
     */
    public function __construct(
        public readonly string $resource,
        public readonly string $x,
        public readonly ?string $y = null,
        public readonly array $options = [],
    ) {
    }
}
