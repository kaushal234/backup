<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\ShopfloorViewItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ShopfloorViewItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ShopfloorViewItem::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        $item = new ShopfloorViewItem();

        $item->level = (int) $data['level'];
        $item->partNumber = IONXmlDecoder::trim($data['partNumber']);
        $item->position = (int) $data['position'];
        $item->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        $item->itemOtherDescription = IONXmlDecoder::trim($data['itemOtherDescription']);
        $item->quantity = (float) $data['quantity'];
        $item->unitOfMeasure = IONXmlDecoder::trim($data['unitOfMeasure']);
        $item->engineeringRevisionEffectiveDate = $data['engineeringRevisionEffectiveDate'];
        $item->engineeringRevisionExpiryDate = $data['engineeringRevisionExpiryDate'];
        $item->engineeringRevision = IONXmlDecoder::trim($data['engineeringRevision']);
        $item->engineeringSignalCode = $data['engineeringSignalCode'];
        $item->extraInformation = $data['extraInformation'];
        $item->operation = (int) $data['operation'];
        $item->warehouse = IONXmlDecoder::trim($data['warehouse']);
        $item->backflushIfMaterial = IONXmlDecoder::enforceYesNoToBoolean($data['backflushIfMaterial']);
        $item->phantom = IONXmlDecoder::enforceYesNoToBoolean($data['phantom']);

        return $item;
    }
}
