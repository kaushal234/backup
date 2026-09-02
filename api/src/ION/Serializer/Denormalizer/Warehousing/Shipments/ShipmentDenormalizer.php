<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing\Shipments;

use App\ION\Resources\Warehousing\Shipments\Shipment;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ShipmentDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'SHIPMENT_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Shipment::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        foreach ([
            'shipFrom' => ['code'], 'shipTo' => ['code'],
            'tracking' => ['carrierTrackingNumber', 'trackingNumber'],
            'references' => ['shipmentReference', 'customerOrder'],
            'shippingDocuments' => ['route', 'deliveryTerms', 'pointOfTitlePassage', 'estimatedFreightCostsCurrency'],
        ] as $object => $properties) {
            if (!isset($data[$object])) {
                continue;
            }
            foreach ($properties as $property) {
                $data[$object][$property] = IONXmlDecoder::trim($data[$object][$property]);
            }
        }

        $data['lines'] = IONXmlDecoder::enforceIndexedCollection($data['lines']['line'] ?? []);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
