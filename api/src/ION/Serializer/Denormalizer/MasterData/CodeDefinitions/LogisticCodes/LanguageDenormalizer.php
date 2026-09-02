<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\CodeDefinitions\LogisticCodes;

use App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes\Language;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class LanguageDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'LANGUAGE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Language::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        IONXmlDecoder::renameKey($data, 'iso639Language', 'iso639');
        IONXmlDecoder::renameKey($data, 'iso3166_1Country', 'codeAlpha2');
        IONXmlDecoder::renameKey($data, 'iso639_2Format', 'iso639_2');

        $data['iso639'] = IONXmlDecoder::trim($data['iso639']);
        $data['codeAlpha2'] = IONXmlDecoder::trim($data['codeAlpha2']);
        $data['iso639_2'] = IONXmlDecoder::trim($data['iso639_2']);

        $data['name'] = \Locale::getDisplayName($data['iso639_2']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
