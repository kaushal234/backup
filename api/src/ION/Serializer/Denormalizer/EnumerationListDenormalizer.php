<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer;

use App\ION\Resources\EnumerationList;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EnumerationListDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ENUMERATION_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return EnumerationList::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        IONXmlDecoder::renameKey($data, 'enums', 'enumerations');
        array_walk_recursive($data, static function (&$value) { $value = mb_trim($value); });

        $data['enumerations'] = IONXmlDecoder::enforceIndexedCollection($data['enumerations']['enum']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
