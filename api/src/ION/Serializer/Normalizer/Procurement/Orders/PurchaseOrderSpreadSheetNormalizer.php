<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\Procurement\Orders;

use ApiPlatform\Metadata\GetCollection;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use App\ION\Resources\Procurement\Orders\PurchaseOrderLine;
use App\Serializer\Encoder\XlsxEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchaseOrderSpreadSheetNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private readonly TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return PurchaseOrder::class === ($context['resource_class'] ?? null) && ($context['operation'] ?? null) instanceof GetCollection && XlsxEncoder::FORMAT === $format;
    }

    /**
     * @param PurchaseOrder[] $objects
     */
    public function normalize($objects, ?string $format = null, array $context = []): array
    {
        $normalizedData = [];
        foreach ($objects as $object) {
            foreach ($object->getLines() as $line) {
                if (PurchaseOrderLine::SUMMARY !== $line->getLineState()) {
                    $normalizedData[] = [
                        // PO information
                        $this->translator->trans('purchase_order.confirmation.fields.id', [], 'emails') => $object->orderIdentifier,
                        $this->translator->trans('purchase_order.confirmation.fields.erp', [], 'emails') => $object->getSiteNumber(),
                        $this->translator->trans('purchase_order.confirmation.fields.supplier_number', [], 'emails') => $object->buyFromSupplierCode,
                        $this->translator->trans('purchase_order.confirmation.fields.order_date', [], 'emails') => $object->orderDatetime->format('Y-m-d'),
                        $this->translator->trans('purchase_order.confirmation.fields.reference_a', [], 'emails') => $object->reference1,
                        $this->translator->trans('purchase_order.confirmation.fields.reference_b', [], 'emails') => $object->reference2,
                        $this->translator->trans('purchase_order.confirmation.fields.po_status', [], 'emails') => $object->isUnconfirmed() ? $object::UNCONFIRMED : $object::CONFIRMED,
                        $this->translator->trans('purchase_order.confirmation.fields.lines_count', [], 'emails') => \count($object->getLines()),

                        // PO line information
                        $this->translator->trans('purchase_order.confirmation.fields.line', [], 'emails') => $line->lineIdentifier,
                        $this->translator->trans('purchase_order.confirmation.fields.sequence', [], 'emails') => $line->sequence,
                        $this->translator->trans('purchase_order.confirmation.fields.part_number', [], 'emails') => $line->getPartNumber(),
                        $this->translator->trans('purchase_order.confirmation.fields.supplier_part_number', [], 'emails') => null !== $line->supplierItemCode ? html_entity_decode($line->supplierItemCode) : '',
                        $this->translator->trans('purchase_order.confirmation.fields.po_line_state', [], 'emails') => $line->getLineState(),
                        $this->translator->trans('purchase_order.confirmation.fields.revision_status', [], 'emails') => $line->isExpired() ? \sprintf('Changed on %s', mb_substr($line->engineeringRevisionExpiryDate, 0, -10)) : 'OK',
                        $this->translator->trans('purchase_order.confirmation.fields.revision', [], 'emails') => $line->engineeringItemRevision ?? $this->translator->trans('generic.not_available', [], 'emails'),
                        $this->translator->trans('purchase_order.confirmation.fields.order_quantity', [], 'emails') => $line->quantity->value,
                        $this->translator->trans('purchase_order.confirmation.fields.delivered_quantity', [], 'emails') => $line->receivedQuantity->value,
                        $this->translator->trans('purchase_order.confirmation.fields.back_order_quantity', [], 'emails') => $line->quantity->value - $line->receivedQuantity->value,
                        $this->translator->trans('purchase_order.confirmation.fields.description', [], 'emails') => $line->description,
                        $this->translator->trans('purchase_order.confirmation.fields.original_requested_date', [], 'emails') => $line->plannedReceiptDate->format('Y-m-d'),
                        $this->translator->trans('purchase_order.confirmation.fields.rescheduled_delivery_date', [], 'emails') => $line->rescheduledDate ? $line->rescheduledDate->format('Y-m-d') : $this->translator->trans('generic.not_available', [], 'emails'),
                        $this->translator->trans('purchase_order.confirmation.fields.confirmed_delivery_date', [], 'emails') => $line->confirmedSupplierDate ? $line->confirmedSupplierDate->format('Y-m-d') : '',
                        $this->translator->trans('purchase_order.confirmation.fields.po_line_status', [], 'emails') => $line->isUnconfirmed() ? $object::UNCONFIRMED : $object::CONFIRMED,
                        $this->translator->trans('purchase_order.confirmation.fields.po_line_delivery_status', [], 'emails') => $this->getDeliveryStatus($line),
                    ];
                }
            }
        }

        return $normalizedData;
    }

    private function getDeliveryStatus(PurchaseOrderLine $orderLine): string
    {
        $status = '';
        if ($orderLine->isLate()) {
            $status = $this->translator->trans('purchase_order.status.late', [], 'emails');
        }
        if ($orderLine->isToBeDeliveredWithin7Days()) {
            $status = '' !== $status ? \sprintf('%s, %s', $status, $this->translator->trans('purchase_order.status.to_be_delivered', [], 'emails')) : $this->translator->trans('purchase_order.status.to_be_delivered', [], 'emails');
        }

        return $status;
    }
}
