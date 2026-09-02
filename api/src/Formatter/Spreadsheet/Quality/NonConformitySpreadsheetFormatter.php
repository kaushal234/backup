<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Quality;

use App\Entity\Parts\NonConformityPart;
use App\Entity\Quality\NonConformity;
use App\Entity\Quality\Process;
use App\Entity\Quality\Responsible;
use App\Entity\Sales\Product;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class NonConformitySpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getColumnToRename(): array
    {
        return [
        ];
    }

    public function getComputedColumns(): array
    {
        return ['products', 'responsibles', 'processes', 'partNumbers', 'quantity', 'partsDescription', 'serialNumbers', 'refTypes', 'references', 'turnAroundTime'];
    }

    /**
     * @param NonConformity $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'products' => implode(' / ', array_map(static fn (Product $product) => (string) $product, $item->getProducts()->toArray())),
            'responsibles' => implode(' / ', array_map(static fn (Responsible $responsible) => (string) $responsible, $item->getResponsibles()->toArray())),
            'processes' => implode(' / ', array_map(static fn (Process $process) => (string) $process, $item->getProcesses()->toArray())),
            'partNumbers' => implode(' / ', array_map(static fn (NonConformityPart $part) => $part->partNumber, $item->getParts()->toArray())),
            'quantity' => implode(' / ', array_map(static fn (NonConformityPart $part) => $part->quantity, $item->getParts()->toArray())),
            'partsDescription' => implode(' / ', array_map(static fn (NonConformityPart $part) => $part->description, $item->getParts()->toArray())),
            'serialNumbers' => implode(' / ', array_map(static fn (NonConformityPart $part) => $part->serialNumber, $item->getParts()->toArray())),
            'refTypes' => implode(' / ', array_map(static fn (NonConformityPart $part) => $part->reference, $item->getParts()->toArray())),
            'references' => implode(' / ', array_map(static fn (NonConformityPart $part) => $part->referenceNumber, $item->getParts()->toArray())),
            'turnAroundTime' => null !== $item->closedAt ? round(($item->closedAt->getTimestamp() - $item->createdAt->getTimestamp()) / 86400) : null,
            default => null
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return NonConformity::class === $class;
    }
}
