<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\ManualItem;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\ManualPart;
use App\ION\Serializer\Denormalizer\Manufacturing\JobShop\SignalCodeDescriptionTrait;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ManualPartDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;
    use SignalCodeDescriptionTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ManualPart::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        $item = new ManualPart();

        $item->partNumber = IONXmlDecoder::trim($data['partNumber']);
        $item->engineeringRevision = IONXmlDecoder::trim($data['engineeringRevision']);
        $item->engineeringSignalCode = $data['engineeringSignalCode'];
        $item->signalCodeDescription = $this->getSignalCodeDescription($data['engineeringSignalCode']);
        $item->position = (int) $data['position'];
        $item->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        $item->itemOtherDescription = IONXmlDecoder::trim($data['itemOtherDescription']);
        $item->quantity = (float) $data['quantity'];

        $data['children'] = IONXmlDecoder::enforceIndexedCollection($data['children']['item'] ?? []);
        foreach ($data['children'] as $child) {
            $item->addItem($this->denormalizer->denormalize($child, ManualItem::class, $format, $context));
        }

        return $item;
    }
}
