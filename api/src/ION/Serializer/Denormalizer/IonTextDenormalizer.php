<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer;

use App\ION\Resources\IonText;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class IonTextDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ION_TEXT_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return IonText::class === $type && !($context[self::ALREADY_CALLED] ?? null);
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

        if (isset($data['site'])) {
            $data['site'] = IONXmlDecoder::trim($data['site']);
        }

        $data['textsByLanguage'] = IONXmlDecoder::enforceIndexedCollection($data['textsByLanguage']['textByLanguage'] ?? []);
        IONXmlDecoder::renameKey($data, 'textsByLanguage', 'textByLanguages');

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
