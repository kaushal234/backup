<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Engineering;

use App\ION\Resources\Engineering\EngineeringItemDescription;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EngineeringItemDescriptionDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ENGINEERING_ITEM_DESCRIPTION_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return EngineeringItemDescription::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['item'] = IONXmlDecoder::trim($data['item']);

        IONXmlDecoder::renameKey($data, 'engineeringDescription', 'description');
        $data['description'] = IONXmlDecoder::trim($data['description']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
