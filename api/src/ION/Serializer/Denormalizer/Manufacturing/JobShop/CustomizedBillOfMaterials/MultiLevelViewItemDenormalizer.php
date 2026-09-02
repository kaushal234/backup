<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\MultiLevelViewItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MultiLevelViewItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return MultiLevelViewItem::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        $item = new MultiLevelViewItem();

        $item->level = (int) $data['level'];
        $item->partNumber = IONXmlDecoder::trim($data['partNumber']);
        $item->position = (int) $data['position'];
        $item->operation = (int) $data['operation'];
        $item->engineeringRevision = IONXmlDecoder::trim($data['engineeringRevision']);
        $item->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        $item->itemOtherDescription = IONXmlDecoder::trim($data['itemOtherDescription']);
        $item->quantity = (float) $data['quantity'];
        $item->unitOfMeasure = IONXmlDecoder::trim($data['unitOfMeasure']);
        $item->engineeringSignalCode = $data['engineeringSignalCode'];
        $item->engineeringRevisionEffectiveDate = $data['engineeringRevisionEffectiveDate'];
        $item->engineeringRevisionExpiryDate = $data['engineeringRevisionExpiryDate'];
        $item->phantom = IONXmlDecoder::enforceYesNoToBoolean($data['phantom']);

        $data['children'] = IONXmlDecoder::enforceIndexedCollection($data['children']['item'] ?? []);
        foreach ($data['children'] as $child) {
            $item->addChild($this->denormalizer->denormalize($child, $type, $format, $context));
        }

        return $item;
    }
}
