<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Engineering;

use App\ION\Resources\Engineering\EngineeringItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EngineeringItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ENGINEERING_ITEM_DENORMALIZER_ALREADY_CALLED';

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return EngineeringItem::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['item'] = IONXmlDecoder::trim($data['item']);
        $data['revisions'] = IONXmlDecoder::enforceIndexedCollection($data['revisions']['revision'] ?? []);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }
}
