<?php

declare(strict_types=1);

namespace App\DataTransferObject\PurchaseOrder;

use App\Sdk\Resource\PurchaseOrder;
use Symfony\Component\Validator\Constraints as Assert;

use function in_array;

final class EditAllPurchaseOrderLine
{
    public const string INVALID_CONFIRMED_DELIVERY_DATE = 'purchase_order.line.edit.confirmed_delivery_date.noLine';

    public string $iri;

    public string $message = '';

    /**
     * @var EditPurchaseOrderLine[]
     */
    #[Assert\NotBlank(message: self::INVALID_CONFIRMED_DELIVERY_DATE)]
    #[Assert\Valid]
    private array $editLines = [];

    public static function fromPurchaseOrder(PurchaseOrder $order): self
    {
        $edit = new self();
        $edit->iri = $order->iri;
        foreach ($order->lines as $line) {
            if ($line->isConfirmable) {
                $edit->addEditLine(EditPurchaseOrderLine::fromLine($line));
            }
        }

        return $edit;
    }

    /**
     * @return EditPurchaseOrderLine[]
     */
    public function getEditLines(): array
    {
        return $this->editLines;
    }

    public function addEditLine(EditPurchaseOrderLine $editLine): void
    {
        if (!in_array($editLine, $this->editLines, true)) {
            $this->editLines[] = $editLine;
        }
    }

    public function removeEditLine(EditPurchaseOrderLine $editLine): void
    {
        foreach ($this->editLines as $key => $line) {
            if ($line === $editLine) {
                unset($this->editLines[$key]);
            }
        }
    }

    public function hasEditedLines(): bool
    {
        foreach ($this->editLines as $line) {
            if ($line->update) {
                return true;
            }
        }

        return false;
    }
}
