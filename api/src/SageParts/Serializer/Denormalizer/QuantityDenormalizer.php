<?php

declare(strict_types=1);

namespace App\SageParts\Serializer\Denormalizer;

use App\SageParts\Resources\Quantity;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class QuantityDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'SAGE_QUANTITY_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return [
            Quantity::class => false,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Quantity::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (empty($data)) {
            return null;
        }

        $data['unit'] = (string) $data['@Unit'];
        $data['quantity'] = (float) $data['#'];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
