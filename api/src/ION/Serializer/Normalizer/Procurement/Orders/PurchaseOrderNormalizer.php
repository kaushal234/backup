<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\Procurement\Orders;

use App\ION\DataProcessor\IONDataProcessor;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PurchaseOrderNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ION_PURCHASE_ORDER_NORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PurchaseOrder && null === ($context[self::ALREADY_CALLED] ?? null) && \in_array(IONDataProcessor::ION_SYNC, $context[AbstractNormalizer::GROUPS] ?? [], true);
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $normalizedData['Line'] = $normalizedData['lines'];
        unset($normalizedData['lines']);

        /* @var array $normalizedData */
        return $normalizedData;
    }
}
