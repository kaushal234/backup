<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\MIS;

use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class TroubleTicketSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getColumnToRename(): array
    {
        return [
            'module.application.name' => 'application',
            'module.operationalOwner' => 'moo',
            'module.name' => 'module',
            'shortDescription' => 'subject',
        ];
    }

    public function supports(string $class, string $operationName): bool
    {
        return TroubleTicket::class === $class;
    }
}
