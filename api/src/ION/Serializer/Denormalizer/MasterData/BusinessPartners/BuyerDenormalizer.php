<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\BusinessPartners;

use App\ION\Resources\MasterData\BusinessPartners\Buyer;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BuyerDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'BUSINESS_PARTNER_BUYER_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Buyer::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['supplierCode'] = IONXmlDecoder::trim($data['businessPartnerCode']);
        $data['erpCode'] = IONXmlDecoder::trim($data['site']);
        $data['department'] = IONXmlDecoder::trim($data['department']);
        $data['buyerCode'] = IONXmlDecoder::trim($data['buyer']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
