<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\MaterialsCsvItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MaterialsCsvItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return MaterialsCsvItem::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        $item = new MaterialsCsvItem();

        $item->partNumber = IONXmlDecoder::trim($data['partNumber']);
        $item->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        $item->itemOtherDescription = IONXmlDecoder::trim($data['itemOtherDescription']);
        $item->unitOfMeasure = IONXmlDecoder::trim($data['unitOfMeasure']);
        $item->engineeringRevision = IONXmlDecoder::trim($data['engineeringRevision']);
        $item->engineeringSignalCode = $data['engineeringSignalCode'];
        $item->quantity = (float) $data['quantity'];
        $item->pmoc = $data['pmoc'];
        $item->itemType = IONXmlDecoder::trim($data['itemType']);
        $item->purchaseStatisticsGroup = IONXmlDecoder::trim($data['purchaseStatisticsGroup']);
        $item->orderQuantityIncrement = (float) $data['orderQuantityIncrement'];
        $item->minimumOrderQuantity = (float) $data['minimumOrderQuantity'];
        $item->safetyStock = (float) $data['safetyStock'];
        $item->supplyTime = (float) $data['supplyTime'];
        $item->buyFromBusinessPartner = IONXmlDecoder::trim($data['buyFromBusinessPartner']);
        $item->buyer = IONXmlDecoder::trim($data['buyer']);
        $item->estimatedStandardCost = (float) $data['estimatedStandardCost'];
        $item->salesPriceGroup = IONXmlDecoder::trim($data['salesPriceGroup']);
        $item->itemGroup = IONXmlDecoder::trim($data['itemGroup']);

        $item->preventive = false !== mb_strpos($data['pmoc'] ?? '', 'p');
        $item->maintenance = false !== mb_strpos($data['pmoc'] ?? '', 'm');
        $item->overhaul = false !== mb_strpos($data['pmoc'] ?? '', 'o');
        $item->critical = false !== mb_strpos($data['pmoc'] ?? '', 'c');

        return $item;
    }
}
