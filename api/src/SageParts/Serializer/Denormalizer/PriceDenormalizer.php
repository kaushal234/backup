<?php

declare(strict_types=1);

namespace App\SageParts\Serializer\Denormalizer;

use App\SageParts\Resources\Price;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PriceDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'SAGE_PRICE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return [
            Price::class => false,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Price::class === $type && !($context[self::ALREADY_CALLED] ?? null);
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

        $data['currency'] = (string) $data['@Currency'];
        $data['price'] = (float) $data['#'];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
