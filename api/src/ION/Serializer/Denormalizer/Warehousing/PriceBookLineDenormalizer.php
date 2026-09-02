<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing;

use App\ION\Resources\Warehousing\PriceBookLine;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PriceBookLineDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PRICE_BOOK_LINE_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return PriceBookLine::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;
        $data['value'] = (float) $data['value'];
        $data['currency'] = (string) $data['currency'];
        $data['effectiveDate'] = (string) $data['effectiveDate'];
        $data['expiryDate'] = (string) $data['expiryDate'];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
