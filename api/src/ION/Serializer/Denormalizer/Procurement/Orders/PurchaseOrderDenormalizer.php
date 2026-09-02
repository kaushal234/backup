<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Procurement\Orders;

use App\ION\DataProvider\AbstractIONDataProvider;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PurchaseOrderDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PURCHASE_ORDER_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return PurchaseOrder::class === $type && !($context[self::ALREADY_CALLED] ?? null) && ($context[AbstractIONDataProvider::ION_RESOURCE_ATTRIBUTE] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (isset($context[AbstractIONDataProvider::ION_RESOURCE_ATTRIBUTE])) {
            $data['lines'] = IONXmlDecoder::enforceIndexedCollection($data['lines']['line'] ?? []);
        }

        $data['buyFromSupplierCode'] = IONXmlDecoder::trim($data['buyFromSupplierCode']);
        $data['reference1'] = IONXmlDecoder::trim($data['reference1']);
        $data['reference2'] = IONXmlDecoder::trim($data['reference2']);

        /* @var PurchaseOrder $purchaseOrder */
        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
