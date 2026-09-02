<?php

declare(strict_types=1);

namespace App\CQRS\Query;

interface ExportableQueryInterface extends QueryInterface
{
    /**
     * @param array<string, mixed> $criteria
     */
    public function toExportQuery(string $filename, string $format, array $criteria): QueryInterface;
}
