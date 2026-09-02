<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Priority is set to -100, so it's the fallback formatter when no specific action is needed
 * to rename fields and format date.
 */
#[AsTaggedItem(priority: -100)]
class GenericSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function supports(string $class, string $operationName): bool
    {
        return true;
    }
}
