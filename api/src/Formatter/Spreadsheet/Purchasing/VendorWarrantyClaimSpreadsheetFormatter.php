<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Purchasing;

use App\Entity\Parts\VendorWarrantyClaimPart;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use App\Entity\Quality\Process;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class VendorWarrantyClaimSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getComputedColumns(): array
    {
        return ['partNumber', 'failureType', 'processes', 'module'];
    }

    /**
     * @param VendorWarrantyClaim $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'partNumber' => implode(' / ', array_map(static fn (VendorWarrantyClaimPart $part) => $part->partNumber, $item->getParts()->toArray())),
            'failureType' => $item instanceof NCRVendorWarrantyClaim ? $item->nonConformity->failureType : null,
            'processes' => $item instanceof NCRVendorWarrantyClaim ? implode(' / ', array_map(static fn (Process $process) => (string) $process, $item->nonConformity->getProcesses()->toArray())) : null,
            'module' => $item instanceof NCRVendorWarrantyClaim ? \sprintf('NCR#%d', $item->nonConformity->getId()) : ($item instanceof WCVendorWarrantyClaim ? \sprintf('WC#%d', $item->warrantyClaimId) : null),
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return VendorWarrantyClaim::class === $class;
    }

    protected function getColumnToRename(): array
    {
        return [
            'supplierCorrectiveActionRequest.id' => 'supplierCorrectiveActionRequest',
            'status.name' => 'status',
        ];
    }
}
