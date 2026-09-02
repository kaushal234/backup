<?php

declare(strict_types=1);

namespace App\CQRS\Query\TechnicianOnCall;

use App\CQRS\Query\ExportableQueryInterface;
use App\CQRS\Query\QueryInterface;

final class FindAllTechnicianOnCallQuery implements ExportableQueryInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public ?int $page = null,
        public ?int $itemsPerPage = null,
        public array $options = [],
    ) {
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function toExportQuery(string $filename, string $format, array $criteria): QueryInterface
    {
        return new ExportTechnicianOnCallQuery($filename, $format, [
            ...$this->options,
            ...$criteria,
        ]);
    }
}
