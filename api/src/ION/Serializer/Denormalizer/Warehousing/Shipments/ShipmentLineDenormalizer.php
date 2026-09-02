<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing\Shipments;

use App\ION\Resources\Warehousing\Shipments\ShipmentLine;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ShipmentLineDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'SHIPMENT_LINE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ShipmentLine::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['item'] = IONXmlDecoder::trim($data['item']);
        $data['itemDescription'] = IONXmlDecoder::trim($data['itemDescription']);
        $data['unitOfMeasure'] = IONXmlDecoder::trim($data['unitOfMeasure']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
