<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing;

use App\ION\Resources\Warehousing\TextItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @deprecated
 */
class TextItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TEXT_ITEM_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return TextItem::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (empty($data['code'])) {
            return null;
        }

        $data['code'] = IONXmlDecoder::trim($data['code']);
        $data['date'] = IONXmlDecoder::trim($data['date']);
        $data['site'] = IONXmlDecoder::trim($data['site']);

        $data['textsByLanguage'] = IONXmlDecoder::enforceIndexedCollection($data['textsByLanguage']['textByLanguage'] ?? []);
        IONXmlDecoder::renameKey($data, 'textsByLanguage', 'textItemLangs');

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
