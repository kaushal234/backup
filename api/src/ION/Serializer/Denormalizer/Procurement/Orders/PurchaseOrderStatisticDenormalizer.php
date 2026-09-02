<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Procurement\Orders;

use App\ION\DataProvider\AbstractIONDataProvider;
use App\ION\Resources\Procurement\Orders\Statistic\PurchaseOrderStatistic;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PurchaseOrderStatisticDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PURCHASE_ORDER_STATISTIC_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return PurchaseOrderStatistic::class === $type && !($context[self::ALREADY_CALLED] ?? null) && ($context[AbstractIONDataProvider::ION_RESOURCE_ATTRIBUTE] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (isset($context[AbstractIONDataProvider::ION_RESOURCE_ATTRIBUTE])) {
            $data['lines'] = IONXmlDecoder::enforceIndexedCollection($data['lines']['line'] ?? []);
            $data['contacts'] = IONXmlDecoder::enforceIndexedCollection($data['contacts']['contact'] ?? []);
            $data['internalContacts'] = IONXmlDecoder::enforceIndexedCollection($data['internalContacts']['contact'] ?? []);
        }

        foreach ($data['lines'] as &$line) {
            $line['late'] = IONXmlDecoder::enforceYesNoToBoolean($line['late']);
            $line['unconfirmed'] = IONXmlDecoder::enforceYesNoToBoolean($line['unconfirmed']);
        }

        $data['buyFromSupplierCode'] = IONXmlDecoder::trim($data['buyFromSupplierCode']);
        $data['buyFromSupplierName'] = IONXmlDecoder::trim($data['buyFromSupplierName']);
        $data['terminated'] = IONXmlDecoder::enforceYesNoToBoolean($data['terminated'] ?? '2');

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
