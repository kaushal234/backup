<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Task;

use App\Entity\BaseTask;
use App\Entity\Task\Task;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class BaseTaskSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getComputedColumns(): array
    {
        return ['type', 'referenceId'];
    }

    /**
     * @param BaseTask $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'type' => $item instanceof Task ? 'Task' : 'Trouble Ticket',
            'referenceId' => $item instanceof Task ? $item->referenceId : null,
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return BaseTask::class === $class;
    }
}
