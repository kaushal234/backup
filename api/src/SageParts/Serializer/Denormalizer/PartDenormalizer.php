<?php

declare(strict_types=1);

namespace App\SageParts\Serializer\Denormalizer;

use App\SageParts\Resources\SagePart;
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
            SagePart::class => false,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return SagePart::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (empty($data) || !\array_key_exists('SageUID', $data)) {
            return null;
        }

        $data['alvestId'] = (string) $data['@MasterID'];
        $data['sageId'] = (string) $data['SageItemId'];
        $data['description'] = $data['ItemDescription'];
        $data['sageUID'] = $data['SageUID'];
        $data['locations'] = $data['Location'];
        unset($data['@MasterID'], $data['ItemDescription'], $data['SageItemId'], $data['SageUID'], $data['Location']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
