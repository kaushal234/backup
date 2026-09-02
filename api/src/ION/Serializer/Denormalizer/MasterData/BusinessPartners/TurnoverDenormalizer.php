<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\BusinessPartners;

use App\ION\Resources\MasterData\BusinessPartners\Turnover;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TurnoverDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'BUSINESS_PARTNER_TURNOVER_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Turnover::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['code'] = IONXmlDecoder::trim($data['businessPartnerCode']);
        $data['name'] = IONXmlDecoder::trim($data['businessPartnerName']);
        $data['countryCode'] = IONXmlDecoder::trim($data['countryCode']);
        $data['turnover'] = (float) $data['turnover'];
        $data['currencyCode'] = IONXmlDecoder::trim($data['currency']);
        $data['currencyName'] = IONXmlDecoder::trim($data['currencyDescription']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
