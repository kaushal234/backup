<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\ShopfloorPdfItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ShopfloorPdfItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ShopfloorPdfItem::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        $item = new ShopfloorPdfItem();

        $item->partNumber = IONXmlDecoder::trim($data['partNumber']);
        $item->position = (int) $data['position'];
        $item->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        $item->itemOtherDescription = IONXmlDecoder::trim($data['itemOtherDescription']);
        $item->quantity = (float) $data['quantity'];
        $item->unitOfMeasure = IONXmlDecoder::trim($data['unitOfMeasure']);
        $item->pmoc = $data['pmoc'];
        $item->preventive = false !== mb_strpos($data['pmoc'] ?? '', 'p');
        $item->maintenance = false !== mb_strpos($data['pmoc'] ?? '', 'm');
        $item->overhaul = false !== mb_strpos($data['pmoc'] ?? '', 'o');
        $item->critical = false !== mb_strpos($data['pmoc'] ?? '', 'c');

        return $item;
    }
}
