<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\Items\ItemClassification;

use App\ION\Resources\MasterData\Items\ItemClassification\ProductClass;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductClassDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'PRODUCT_CLASS_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ProductClass::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if ('' === IONXmlDecoder::trim($data['code'])) {
            return null;
        }

        $data['code'] = IONXmlDecoder::trim($data['code']);
        $data['name'] = IONXmlDecoder::trim($data['name']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
