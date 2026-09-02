<?php

declare(strict_types=1);

namespace App\SageParts\Serializer\Denormalizer;

use App\SageParts\Resources\Location;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class LocationDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'SAGE_LOCATION_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return [
            Location::class => false,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Location::class === $type && !($context[self::ALREADY_CALLED] ?? null);
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

        $data['name'] = (string) $data['@Name'];
        $data['onHand'] = $data['OnHand'];
        $data['onOrder'] = $data['OnOrder'];
        $data['unitPrice'] = $data['UnitPrice'];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
