<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Communication;

use App\Entity\Sales\ExtranetUser;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class ContactCampaignSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getColumnToRename(): array
    {
        return [
            'extranetUserProfile.customer.name' => 'customer',
            'extranetUserProfile.country.name' => 'country',
            'isVerified' => 'information verified',
        ];
    }

    public function supports(string $class, string $operationName): bool
    {
        return ExtranetUser::class === $class && 'get_campaign_contacts' === $operationName;
    }
}
