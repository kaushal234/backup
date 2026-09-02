<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders;

use App\ION\DataProcessor\IONDataProcessor;
use App\ION\Resources\IonText;
use App\ION\Resources\Manufacturing\ItemRevisionStatus;
use App\ION\Resources\RevisionDateInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PurchaseOrderLine implements PurchaseOrderLineInterface, RevisionDateInterface
{
    /**
     * @var string
     */
    final public const SUMMARY = 'summary';

    /**
     * @var string
     */
    final public const TO_BE_CANCELED = 'to be canceled';
    /**
     * @var string
     */
    final public const CANCELED = 'canceled';

    /**
     * @var string
     */
    final public const DELIVERED = 'delivered';

    /**
     * @var string
     */
    final public const OPEN = 'open';

    #[Groups(['purchase_order', 'purchase_order:write', IONDataProcessor::ION_SYNC])]
    public string $lineIdentifier;

    #[Groups(['purchase_order', 'purchase_order:write', IONDataProcessor::ION_SYNC])]
    public int $sequence;

    #[Groups(['purchase_order'])]
    public ?string $projectCode = null;

    #[Groups(['purchase_order'])]
    public ?int $site = null;

    #[Groups(['purchase_order'])]
    public string $itemCode;

    #[Groups(['purchase_order'])]
    public ?string $supplierItemCode = null;
    #[Groups(['purchase_order'])]
    public ?string $engineeringItemRevision = null;

    #[Groups(['purchase_order'])]
    public ?string $engineeringRevisionEffectiveDate = null;

    #[Groups(['purchase_order'])]
    public ?string $engineeringRevisionExpiryDate = null;

    #[Groups(['purchase_order'])]
    public ?string $project = null;

    #[Groups(['purchase_order'])]
    public \DateTimeInterface $plannedReceiptDate;

    /**
     * In LN interface, it is called "receipt date for pricing".
     */
    #[Groups(['purchase_order'])]
    public \DateTimeInterface $currentPlannedReceiptDate;

    /**
     * confirmedSupplierDate doesnt exist on LN. its a mix on ( currentReceiptDate/confirmedReceiptDate/changedReceiptDate )
     * see documentation : https://alvestgroup.atlassian.net/wiki/spaces/DEV/pages/2378760212/WIP+Purchase+Order+workflow+new+evendors+LN.
     */
    #[Groups(['purchase_order', 'purchase_order:write', IONDataProcessor::ION_SYNC])]
    #[Assert\NotNull]
    public ?\DateTimeInterface $confirmedSupplierDate = null;

    #[Groups(['purchase_order'])]
    public ?\DateTimeInterface $rescheduledDate = null;

    #[Groups(['purchase_order'])]
    public Quantity $quantity;

    #[Groups(['purchase_order'])]
    public Quantity $receivedQuantity;

    #[Groups(['purchase_order'])]
    public Quantity $backOrderQuantity;

    /**
     * Quantity of labels set for printing label.
     */
    public float $quantityLabel = 0;

    /**
     * Packing slip.
     */
    public string $packingSlip;

    /**
     * Quantity delivered set for printing label.
     */
    public int $labelDeliveredQuantity = 0;

    #[Groups(['purchase_order'])]
    public Amount $price;

    #[Groups(['purchase_order'])]
    public string $description;

    #[Groups(['purchase_order'])]
    public bool $isConfirmable;

    #[Groups(['purchase_order'])]
    public ?bool $isToCancel = false;

    #[Groups(['purchase_order'])]
    public ?bool $canceled = false;

    #[Groups(['purchase_order', 'purchase_order:details'])]
    public ?IonText $lineTexts = null;

    #[Groups(['purchase_order'])]
    public function isLate(): bool
    {
        $today = new \DateTime('today');
        $lateComparisonDate = $this->rescheduledDate ?? $this->confirmedSupplierDate ?? $this->currentPlannedReceiptDate;

        return $this->isConfirmable && $lateComparisonDate < $today;
    }

    #[Groups(['purchase_order'])]
    public function isUnconfirmed(): bool
    {
        return $this->isConfirmable && null === $this->confirmedSupplierDate;
    }

    #[Groups(['purchase_order'])]
    public function isToBeDeliveredWithin7Days(): bool
    {
        if (!$this->isConfirmable) {
            return false;
        }

        $comparedDate = $this->rescheduledDate ?? $this->confirmedSupplierDate ?? $this->currentPlannedReceiptDate;
        $today = new \DateTime('today');

        return $comparedDate > $today && $today->diff($comparedDate)->days <= 7;
    }

    public function getPartNumber(): string
    {
        return $this->itemCode;
    }

    #[Groups(['purchase_order'])]
    public function isExpired(): bool
    {
        return ItemRevisionStatus::expired($this->engineeringRevisionExpiryDate);
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        $validateSupplierDeliveryDate = (new \DateTime('now'))->setTime(0, 0, 0, 0);

        if ($this->confirmedSupplierDate < $validateSupplierDeliveryDate) {
            $context->buildViolation('Please set the delivery date in the future')
                ->atPath('confirmedSupplierDate')
                ->addViolation();
        }
    }

    public function getDate(): ?string
    {
        return $this->engineeringRevisionEffectiveDate;
    }

    #[Groups(['purchase_order'])]
    public function getLineState(): string
    {
        if ($this->canceled) {
            return self::CANCELED;
        }

        if (!$this->sequence) {
            return self::SUMMARY;
        }

        if ($this->isToCancel) {
            return self::TO_BE_CANCELED;
        }

        if ($this->isConfirmable) {
            return self::OPEN;
        }

        return self::DELIVERED;
    }
}
