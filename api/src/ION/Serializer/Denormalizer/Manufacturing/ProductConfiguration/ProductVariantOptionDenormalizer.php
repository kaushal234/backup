<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\ProductConfiguration;

use App\ION\Resources\Manufacturing\ProductConfiguration\ProductVariantOption;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductVariantOptionDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PRODUCT_VARIANT_OPTION_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ProductVariantOption::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['productFeature'] = IONXmlDecoder::trim($data['productFeature']);
        $data['description'] = IONXmlDecoder::trim($data['description']);
        $data['option'] = IONXmlDecoder::trim($data['option']);
        $data['optionDescriptionByProductFeature'] = IONXmlDecoder::trim($data['optionDescriptionByProductFeature']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
