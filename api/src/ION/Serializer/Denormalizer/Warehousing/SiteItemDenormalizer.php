<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing;

use App\ION\Resources\Warehousing\SiteItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SiteItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'SITE_ITEM_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return SiteItem::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['site'] = IONXmlDecoder::trim($data['site']);
        $data['itemCodeSignal'] = IONXmlDecoder::trim($data['itemCodeSignal']);
        $data['weight'] = (float) $data['weight'];
        $data['weightUnitOfMeasure'] = (string) IONXmlDecoder::trim($data['weightUnitOfMeasure']);
        $data['warehouses'] = IONXmlDecoder::enforceIndexedCollection($data['warehouses']['warehouse'] ?? []);
        IONXmlDecoder::renameKey($data, 'itemText', 'textItem');
        IONXmlDecoder::renameKey($data, 'itemCodeSignal', 'codeSignal');

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
