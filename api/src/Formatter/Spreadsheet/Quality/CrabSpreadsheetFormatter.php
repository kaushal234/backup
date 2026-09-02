<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Quality;

use App\Entity\Quality\Crab;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;
use LegacyBundle\Manager\CrabManager;

class CrabSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function __construct(
        private readonly CrabManager $manager,
    ) {
    }

    public function getColumnToRename(): array
    {
        return [
            'equipmentRecord.manufacturerLocation.name' => 'factory',
            'department.name' => 'department',
            'part.partNumber' => 'pn',
            'equipmentRecord.serialNumber' => 'serial number',
            'equipmentRecord.model' => 'model',
            'code.code' => 'code',
            'category' => 'stage',
        ];
    }

    public function getComputedColumns(): array
    {
        return ['questionParentId', 'questionSubject', 'questionDescription', 'code.code'];
    }

    /**
     * @param Crab $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        $piQuestionDetails = $this->manager->getPiQuestionDetails($item);

        return match ($column) {
            'questionSubject' => $piQuestionDetails['subject_en'] ?? null,
            'questionDescription' => $piQuestionDetails['desc_en'] ?? null,
            'questionParentId' => $item->piQuestionParentId,
            'code.code' => null !== $item->code ? \sprintf('%s - %s', $item->code->code, $item->code->description) : null,
            default => null
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return Crab::class === $class;
    }
}
