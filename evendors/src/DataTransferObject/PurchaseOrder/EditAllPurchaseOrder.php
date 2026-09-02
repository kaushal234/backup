<?php

declare(strict_types=1);

namespace App\DataTransferObject\PurchaseOrder;

use App\Sdk\Resource\PurchaseOrder;

use function in_array;

final class EditAllPurchaseOrder
{
    public string $iri;

    public string $message;

    /**
     * @var EditAllPurchaseOrderLine[]
     */
    private array $editPurchaseOrders = [];

    /**
     * @param PurchaseOrder[] $orders
     */
    public static function fromPurchaseOrderCollection(iterable $orders): self
    {
        $edit = new self();
        foreach ($orders as $order) {
            $edit->addEditPurchaseOrders(EditAllPurchaseOrderLine::fromPurchaseOrder($order));
        }

        return $edit;
    }

    /**
     * @return EditAllPurchaseOrderLine[]
     */
    public function getEditPurchaseOrders(): array
    {
        return $this->editPurchaseOrders;
    }

    public function addEditPurchaseOrders(EditAllPurchaseOrderLine $editOrder): void
    {
        if (!in_array($editOrder, $this->editPurchaseOrders, true)) {
            $this->editPurchaseOrders[] = $editOrder;
        }
    }
}
