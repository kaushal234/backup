<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop;

use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterialsItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CustomizedBillOfMaterialsItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use ConflictItemTextsTrait;
    use DenormalizerAwareTrait;
    use SignalCodeDescriptionTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return CustomizedBillOfMaterialsItem::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        CustomizedBillOfMaterialsDenormalizer::setPMOC($data);

        $item = new CustomizedBillOfMaterialsItem();
        $item->unitOfMeasure = IONXmlDecoder::trim($data['unitOfMeasure'], true);
        $item->itemSignalCode = IONXmlDecoder::trim($data['itemSignalCode']);
        $item->itemDescription = IONXmlDecoder::trim($data['itemDescription'], true);
        $item->itemOtherDescription = IONXmlDecoder::trim($data['itemOtherDescription']);
        $item->itemSelectionCode = IONXmlDecoder::trim($data['itemSelectionCode'], true);
        $item->itemType = IONXmlDecoder::trim($data['itemType'], true);
        $item->itemGroup = IONXmlDecoder::trim($data['itemGroup'], true);
        $item->customized = IONXmlDecoder::enforceYesNoToBoolean($data['customized']);
        $item->extraInformation = IONXmlDecoder::trim($data['extraInformation']);
        $item->purchaseStatisticsGroup = IONXmlDecoder::trim($data['purchaseStatisticsGroup'], true);
        $item->buyFromBusinessPartner = IONXmlDecoder::trim($data['buyFromBusinessPartner']);
        $item->buyFromBusinessPartnerName = IONXmlDecoder::trim($data['buyFromBusinessPartnerName']);
        $item->buyer = IONXmlDecoder::trim($data['buyer']);
        $item->supplyTime = '' === IONXmlDecoder::trim($data['supplyTime']) ? null : (int) $data['supplyTime'];
        $item->engineeringRevision = IONXmlDecoder::trim($data['engineeringRevision'], true);
        $item->engineeringRevisionEffectiveDate = IONXmlDecoder::trim($data['engineeringRevisionEffectiveDate'], true);
        $item->engineeringRevisionExpiryDate = IONXmlDecoder::trim($data['engineeringRevisionExpiryDate'], true);
        $item->engineeringRevisionDescription = IONXmlDecoder::trim($data['engineeringRevisionDescription'], true);
        $item->engineeringRevisionDrawing = IONXmlDecoder::trim($data['engineeringRevisionDrawing'], true);
        $item->engineeringSignalCode = IONXmlDecoder::trim($data['engineeringSignalCode']);
        $item->engineeringDescription = IONXmlDecoder::trim($data['engineeringDescription'], true);
        $item->engineeringOtherDescription = IONXmlDecoder::trim($data['engineeringOtherDescription']);
        $item->engineeringSelectionCode = IONXmlDecoder::trim($data['engineeringSelectionCode'], true);
        $item->orderQuantityIncrement = '' === IONXmlDecoder::trim($data['orderQuantityIncrement']) ? null : (int) $data['orderQuantityIncrement'];
        $item->minimumOrderQuantity = '' === IONXmlDecoder::trim($data['minimumOrderQuantity']) ? null : (int) $data['minimumOrderQuantity'];
        $item->safetyStock = '' === IONXmlDecoder::trim($data['safetyStock']) ? null : (int) $data['safetyStock'];
        $item->warehouse = IONXmlDecoder::trim($data['warehouse']);
        $item->salesPriceGroup = IONXmlDecoder::trim($data['salesPriceGroup'], true);
        $item->estimatedStandardCost = '' === IONXmlDecoder::trim($data['estimatedStandardCost']) ? null : (float) $data['estimatedStandardCost'];
        $item->backflushIfMaterial = IONXmlDecoder::enforceYesNoToBoolean($data['backflushIfMaterial']);
        $item->phantom = IONXmlDecoder::enforceYesNoToBoolean($data['phantom']);
        $item->signalCodeDescription = $this->getSignalCodeDescription(IONXmlDecoder::trim($data['engineeringSignalCode']));
        $item->preventive = $data['preventive'];
        $item->maintenance = $data['maintenance'];
        $item->overhaul = $data['overhaul'];
        $item->critical = $data['critical'];
        $item->standardItemProject = IONXmlDecoder::trim($data['standardItemProject'], true);
        $item->standardItem = $data['standardItem'];
        $item->position = (int) $data['position'];
        $item->partNumberProject = IONXmlDecoder::trim($data['partNumberProject']);
        $item->partNumber = $data['partNumber'];
        $item->quantity = (float) $data['quantity'];
        $item->productQuantity = (float) $data['pbomQuantity'];
        $item->level = (int) $data['level'];
        $item->operation = $data['operation'];
        $item->customOperation = (string) $data['customOperation'];

        $data['children'] = IONXmlDecoder::enforceIndexedCollection($data['children']['customizedBillOfMaterialItem'] ?? []);
        foreach ($data['children'] as $child) {
            $itemChild = $this->denormalizer->denormalize($child, $type, $format, $context);
            $item->addChild($itemChild);
            $this->denormalizeConflictItemTexts($child, $itemChild, $format, $context);
        }

        return $item;
    }
}
