<?php

declare(strict_types=1);

namespace App\CQRS\Command\PurchaseOrder;

use App\CQRS\Command\CommandInterface;
use App\DataTransferObject\PurchaseOrder\EditAllPurchaseOrderLine;

final class EditPurchaseOrderAllLineCommand implements CommandInterface
{
    public function __construct(
        public readonly EditAllPurchaseOrderLine $editAllPurchaseOrderLine,
    ) {
    }
}
