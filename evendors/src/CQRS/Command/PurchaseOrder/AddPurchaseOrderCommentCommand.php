<?php

declare(strict_types=1);

namespace App\CQRS\Command\PurchaseOrder;

use App\CQRS\Command\CommandInterface;

final class AddPurchaseOrderCommentCommand implements CommandInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $message,
    ) {
    }
}
