<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing;

use App\ION\Resources\Warehousing\Warehouse;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class WarehouseDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'WAREHOUSE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Warehouse::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['code'] = IONXmlDecoder::trim($data['code']);
        $data['name'] = IONXmlDecoder::trim($data['name']);
        $data['reorderPoint'] = (float) $data['reorderPoint'];
        $data['safetyStock'] = (float) $data['safetyStock'];
        $data['inventoryOnHand'] = (float) $data['inventoryOnHand'];
        $data['inventoryOnOrder'] = (float) $data['inventoryOnOrder'];
        $data['itemSafety'] = (float) $data['itemSafety'];
        $data['allocated'] = (float) $data['allocated'];
        $data['purchasePrice'] = (float) $data['purchasePrice'];
        $data['standardCost'] = (float) $data['standardCost'];
        $data['lastPurchasePriceDate'] = ('' === IONXmlDecoder::trim($data['lastPurchasePriceDate'])) ? null : $data['lastPurchasePriceDate'];
        $data['includeInEnterprisePlanning'] = IONXmlDecoder::enforceYesNoToBoolean($data['includeInEnterprisePlanning']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
