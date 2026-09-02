<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing;

use App\ION\Resources\Warehousing\TextItemLang;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @deprecated
 */
class TextItemLangDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TEXT_ITEM_LANG_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return TextItemLang::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['lang'] = IONXmlDecoder::trim($data['lang']);
        $data['name'] = IONXmlDecoder::trim($data['name']);
        $data['texts'] = IONXmlDecoder::enforceIndexedCollection(\is_string($data['texts']['text']) ? [$data['texts']['text']] : $data['texts']['text']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
