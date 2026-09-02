<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\MIS;

use App\Entity\MIS\Project\Project;
use App\Entity\MIS\Project\ProjectTag;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class ProjectSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getComputedColumns(): array
    {
        return ['tags'];
    }

    /**
     * @param Project $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'tags' => implode(' / ', array_map(static fn (ProjectTag $tag) => (string) $tag, $item->getTags()->toArray())),
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return Project::class === $class;
    }
}
