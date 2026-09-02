<?php

declare(strict_types=1);

namespace App\ION\Dto\Procurement\Orders;

use Symfony\Component\Serializer\Attribute\Groups;

class PurchaseOrderPdfLineInput
{
    #[Groups(['purchase_order:pdf_input'])]
    public string $lineIdentifier;

    #[Groups(['purchase_order:pdf_input'])]
    public int $sequence;

    #[Groups(['purchase_order:pdf_input'])]
    public float $quantityLabel;

    #[Groups(['purchase_order:pdf_input'])]
    public int $labelDeliveredQuantity;

    #[Groups(['purchase_order:pdf_input'])]
    public string $packingSlip;
}
