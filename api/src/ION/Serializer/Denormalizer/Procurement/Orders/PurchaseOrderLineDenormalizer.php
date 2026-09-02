<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Procurement\Orders;

use App\ION\DataProvider\AbstractIONDataProvider;
use App\ION\Resources\IonText;
use App\ION\Resources\Procurement\Orders\Amount;
use App\ION\Resources\Procurement\Orders\PurchaseOrderLine;
use App\ION\Resources\Procurement\Orders\Quantity;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PurchaseOrderLineDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PURCHASE_ORDER_LINE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return PurchaseOrderLine::class === $type && !($context[self::ALREADY_CALLED] ?? null) && ($context[AbstractIONDataProvider::ION_RESOURCE_ATTRIBUTE] ?? null);
    }

    /**
     * @return array|mixed|object
     *
     * @throws ExceptionInterface
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;
        $item = new PurchaseOrderLine();
        $item->lineIdentifier = $data['lineIdentifier'];
        $item->sequence = (int) $data['sequence'];
        $item->site = (int) IONXmlDecoder::trim($data['site'], true);
        $item->itemCode = IONXmlDecoder::trim($data['itemCode']);
        $item->supplierItemCode = IONXmlDecoder::trim($data['supplierItemCode'], true);
        $item->engineeringItemRevision = IONXmlDecoder::trim($data['engineeringItemRevision'], true);
        $item->engineeringRevisionEffectiveDate = IONXmlDecoder::trim($data['engineeringRevisionEffectiveDate'], true);
        $item->engineeringRevisionExpiryDate = IONXmlDecoder::trim($data['engineeringRevisionExpiryDate'], true);
        $item->project = IONXmlDecoder::trim($data['project']);
        $item->plannedReceiptDate = new \DateTime(IONXmlDecoder::trim($data['plannedReceiptDate'], true));
        $item->currentPlannedReceiptDate = new \DateTime(IONXmlDecoder::trim($data['currentPlannedReceiptDate'], true));
        $item->confirmedSupplierDate = !empty($data['confirmedSupplierDate']) ? new \DateTime(IONXmlDecoder::trim($data['confirmedSupplierDate'], true)) : null;
        $item->rescheduledDate = !empty($data['rescheduledDate']) ? new \DateTime(IONXmlDecoder::trim($data['rescheduledDate'], true)) : null;
        $item->quantity = new Quantity();
        $item->quantity->value = (float) $data['quantity']['value'];
        $item->quantity->unitOfMeasure = $data['quantity']['unitOfMeasure'];

        $item->receivedQuantity = new Quantity();
        $item->receivedQuantity->value = (float) $data['receivedQuantity']['value'];
        $item->receivedQuantity->unitOfMeasure = $data['receivedQuantity']['unitOfMeasure'];

        $item->backOrderQuantity = new Quantity();
        $item->backOrderQuantity->value = (float) $data['backOrderQuantity']['value'];
        $item->backOrderQuantity->unitOfMeasure = $data['backOrderQuantity']['unitOfMeasure'];

        $item->price = new Amount();
        $item->price->value = (float) $data['price']['value'];
        $item->price->currency = $data['price']['currency'];
        $item->price->unitOfMeasure = $data['price']['unitOfMeasure'];

        $item->description = IONXmlDecoder::trim($data['description']);
        $item->isConfirmable = IONXmlDecoder::enforceYesNoToBoolean($data['isConfirmable']);

        if (\array_key_exists('toCancel', $data)) {
            $item->isToCancel = IONXmlDecoder::enforceYesNoToBoolean($data['toCancel']);
        }
        if (\array_key_exists('canceled', $data)) {
            $item->canceled = IONXmlDecoder::enforceYesNoToBoolean($data['canceled']);
        }

        $item->project = IONXmlDecoder::trim($data['project'], true);

        if (\array_key_exists('itemText', $data)) {
            IONXmlDecoder::renameKey($data, 'itemText', 'lineTexts');
            $item->lineTexts = $this->denormalizer->denormalize($data['lineTexts'], IonText::class);
        }

        return $item;
    }
}
