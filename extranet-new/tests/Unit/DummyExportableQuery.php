<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\CQRS\Query\ExportableQueryInterface;
use App\CQRS\Query\QueryInterface;

class DummyExportableQuery implements ExportableQueryInterface
{
    public ?string $exportFilename = null;
    public ?string $exportFormat = null;

    /**
     * @var array<string, mixed>
     */
    public array $exportCriteria = [];

    /**
     * @param array<string, mixed> $criteria
     */
    public function toExportQuery(string $filename, string $format, array $criteria): QueryInterface
    {
        $this->exportFilename = $filename;
        $this->exportFormat = $format;
        $this->exportCriteria = $criteria;

        return new DummyQuery(options: $criteria);
    }
}
