<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Sales;

use App\ION\Resources\Sales\SalesOrderLine;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SalesOrderLineDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'SALES_ORDER_LINE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return SalesOrderLine::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['lineID'] = IONXmlDecoder::trim($data['orderLine']);
        $data['item'] = IONXmlDecoder::trim($data['item']);
        $data['plannedDeliveryDate'] = IONXmlDecoder::trim($data['orderLinePromisedDeliveryDate']);
        $data['site'] = $data['UserArea']['site'] ?? null;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
