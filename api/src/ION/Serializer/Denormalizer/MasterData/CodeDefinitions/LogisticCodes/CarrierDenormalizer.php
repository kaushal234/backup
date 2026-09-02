<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\CodeDefinitions\LogisticCodes;

use App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes\Carrier;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CarrierDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'CARRIER_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Carrier::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['name'] = IONXmlDecoder::trim($data['name']);
        $data['url'] = IONXmlDecoder::trim($data['url'], true);
        $data['buyFromBusinessPartner'] = '' === ($data['buyFromBusinessPartner'] ?? '') ? null : $data['buyFromBusinessPartner'];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
