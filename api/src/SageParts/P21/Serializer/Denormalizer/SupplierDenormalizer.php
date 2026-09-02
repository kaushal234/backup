<?php

declare(strict_types=1);

namespace App\SageParts\P21\Serializer\Denormalizer;

use App\SageParts\P21\Resources\Supplier;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SupplierDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'SAGE_SUPPLIER_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return [
            Supplier::class => false,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Supplier::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $part['code'] = (string) $data['SupplierId'];
        $part['name'] = (string) $data['supplier_name'];
        $part['buyerEmail'] = (string) $data['SageBuyerEmail'];

        return $this->denormalizer->denormalize($part, $type, $format, $context);
    }
}
