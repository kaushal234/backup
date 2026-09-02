<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\MIS;

use App\Entity\Module\ChangeLog;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class ChangeLogSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getColumnToRename(): array
    {
        return [
            'module.operationalOwner' => 'moo',
            'ticket' => 'tts',
        ];
    }

    public function supports(string $class, string $operationName): bool
    {
        return ChangeLog::class === $class;
    }
}
