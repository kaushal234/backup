<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Task;

use App\Entity\Task\Task;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class TaskSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getComputedColumns(): array
    {
        return ['escalationTrigger'];
    }

    /**
     * @param Task $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'escalationTrigger' => \sprintf('%s %s', $item->escalationTrigger, $item->escalationTriggerUnit),
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return Task::class === $class;
    }
}
