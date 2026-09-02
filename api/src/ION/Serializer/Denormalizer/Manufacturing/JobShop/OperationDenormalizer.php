<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop;

use App\ION\Resources\Manufacturing\JobShop\Operation;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class OperationDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'OPERATION_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Operation::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        IONXmlDecoder::renameKey($data, 'operation', 'operationIdentifier');
        $data['reference'] = IONXmlDecoder::trim($data['reference']);
        $data['referenceDescription'] = IONXmlDecoder::trim($data['referenceDescription']);
        $data['task'] = IONXmlDecoder::trim($data['task']);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
