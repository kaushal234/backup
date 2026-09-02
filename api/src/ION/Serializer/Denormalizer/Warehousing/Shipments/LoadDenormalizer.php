<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing\Shipments;

use App\ION\Resources\Warehousing\Shipments\Load;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class LoadDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'LOAD_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Load::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['route'] = IONXmlDecoder::trim($data['route']);
        $data['trackingNumber'] = IONXmlDecoder::trim($data['trackingNumber']);

        if ('' === $data['carrier']) {
            $data['carrier'] = null;
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
