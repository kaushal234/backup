<?php

declare(strict_types=1);

namespace App\ION\Dto\Procurement\Orders;

use Symfony\Component\Serializer\Attribute\Groups;

class PurchaseOrderPdfInput
{
    #[Groups(['purchase_order:pdf_input'])]
    private array $lines = [];

    public function getLines(): array
    {
        return $this->lines;
    }

    public function addLine(PurchaseOrderPdfLineInput $purchaseOrderPdfLineInput): self
    {
        $this->lines[] = $purchaseOrderPdfLineInput;

        return $this;
    }

    public function removeLine(PurchaseOrderPdfLineInput $purchaseOrderPdfLineInput): self
    {
        // do nothing
        return $this;
    }
}
