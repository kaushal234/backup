<?php

declare(strict_types=1);

namespace App\DataTransferObject\PurchaseOrder;

use App\Sdk\Resource\PurchaseOrderLine;
use DateTimeImmutable;

final class EditPurchaseOrderLine
{
    public string $lineIdentifier;
    public int $sequence;
    public bool $isConfirmable;

    // only $update editLine are control on assert and send to API
    public bool $update = false;

    private ?DateTimeImmutable $confirmedSupplierDate;

    public static function fromLine(PurchaseOrderLine $line): self
    {
        $edit = new self();
        $edit->lineIdentifier = $line->lineIdentifier;
        $edit->sequence = $line->sequence;
        $edit->confirmedSupplierDate = $line->confirmedSupplierDate ? new DateTimeImmutable($line->confirmedSupplierDate) : null;
        $edit->isConfirmable = $line->isConfirmable;

        return $edit;
    }

    public function getConfirmedSupplierDate(): ?DateTimeImmutable
    {
        return $this->confirmedSupplierDate;
    }

    public function setConfirmedSupplierDate(?DateTimeImmutable $confirmedSupplierDate): void
    {
        $this->confirmedSupplierDate = $confirmedSupplierDate;
    }

    public function isUpdatedConfirmedDate(?string $newConfirmedSupplierDate): bool
    {
        $newConfirmedSupplierDate = '' !== $newConfirmedSupplierDate ? $newConfirmedSupplierDate : null;
        $previousConfirmedDeliveryDate = $this->confirmedSupplierDate?->format('Y-m-d');

        return $previousConfirmedDeliveryDate !== $newConfirmedSupplierDate;
    }
}
