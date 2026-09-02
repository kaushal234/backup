<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\Items\ItemClassification;

use App\ION\Resources\MasterData\Items\ItemClassification\ItemByVendor;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'VENDOR_ITEM_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ItemByVendor::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (empty($data)) {
            return null;
        }

        $data['businessPartnerItem'] = IONXmlDecoder::trim($data['businessPartnerItem']);
        $data['references'] = IONXmlDecoder::enforceIndexedCollection($data['references']['reference'] ?? []);
        IONXmlDecoder::renameKey($data, 'businessPartnerItem', 'item');

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
