<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\SpartaExtensions\Times;

use App\ION\Dto\SpartaExtensions\Times\TimeKeepingPostTransactionOutput;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TimeKeepingPostTransactionOutputDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TIME_KEEPING_POST_TRANSACTION_OUTPUT_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return TimeKeepingPostTransactionOutput::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['lines'] = IONXmlDecoder::enforceIndexedCollection($data['TimeKeepingPostTransactions']['TimeKeepingPostTransaction'] ?? []);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
