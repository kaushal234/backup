<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\ProductConfiguration;

use App\ION\Resources\Manufacturing\ProductConfiguration\ProductVariant;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductVariantsDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PRODUCT_VARIANT_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ProductVariant::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['description'] = IONXmlDecoder::trim($data['description']);
        $data['item'] = IONXmlDecoder::trim($data['item']);
        $data['referenceOrder'] = IONXmlDecoder::trim($data['referenceOrder']);
        $data['options'] = IONXmlDecoder::enforceIndexedCollection($data['options']['option'] ?? []);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
