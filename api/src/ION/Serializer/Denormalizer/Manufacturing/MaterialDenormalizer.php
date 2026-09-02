<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing;

use App\ION\Resources\Manufacturing\Material;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MaterialDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'MATERIAL_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Material::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['item'] = IONXmlDecoder::trim($data['item']);
        $data['itemDescription'] = IONXmlDecoder::trim($data['itemDescription']);
        $data['itemOtherDescription'] = IONXmlDecoder::trim($data['itemOtherDescription']);
        $data['unitOfMeasure'] = IONXmlDecoder::trim($data['unitOfMeasure']);
        $data['revision'] = IONXmlDecoder::trim($data['revision']);
        $data['costPrice'] = (null === IONXmlDecoder::trim($data['costPrice'], true)) ? null : (float) $data['costPrice'];
        $data['inventoryOnHand'] = (int) $data['inventoryOnHand'];
        $data['inventoryOnOrder'] = (int) $data['inventoryOnOrder'];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
