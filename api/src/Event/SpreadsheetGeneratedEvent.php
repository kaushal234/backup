<?php

declare(strict_types=1);

namespace App\Event;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Contracts\EventDispatcher\Event;

class SpreadsheetGeneratedEvent extends Event
{
    private readonly Spreadsheet $spreadsheet;
    private readonly array $context;

    public function __construct(Spreadsheet $spreadsheet, array $context)
    {
        $this->spreadsheet = $spreadsheet;
        $this->context = $context;
    }

    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
