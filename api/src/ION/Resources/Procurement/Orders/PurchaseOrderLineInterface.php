<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders;

interface PurchaseOrderLineInterface
{
    public function getPartNumber(): string;
}
