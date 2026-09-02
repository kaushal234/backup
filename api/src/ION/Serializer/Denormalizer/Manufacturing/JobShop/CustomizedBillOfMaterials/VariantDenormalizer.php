<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Variant;
use App\ION\Resources\Manufacturing\ProductConfiguration\ProductVariant;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class VariantDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'CUSTOMIZED_BILL_OF_MATERIAL_VARIANT_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Variant::class === $type && !($context[self::ALREADY_CALLED] ?? null);
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

        $data['project'] = IONXmlDecoder::trim($data['project']);
        $data['items'] = IONXmlDecoder::enforceIndexedCollection($data['items']['item'] ?? []);

        if (\in_array(ProductVariant::NORMALIZATION_GROUP, $context[AbstractObjectNormalizer::GROUPS], true)) {
            $data['productVariants'] = IONXmlDecoder::enforceIndexedCollection($data['variants']['variant'] ?? []);
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
