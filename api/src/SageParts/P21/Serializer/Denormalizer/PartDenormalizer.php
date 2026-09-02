<?php

declare(strict_types=1);

namespace App\SageParts\P21\Serializer\Denormalizer;

use App\SageParts\P21\Resources\Part;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PartDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'SAGE_PART_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return [
            Part::class => false,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Part::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $part['item'] = mb_trim($data['ItemId']);
        $part['itemDescription'] = (string) $data['ItemDesc'];
        $part['unitOfMeasure'] = (string) $data['UnitOfMeasure'];

        return $this->denormalizer->denormalize($part, $type, $format, $context);
    }
}
