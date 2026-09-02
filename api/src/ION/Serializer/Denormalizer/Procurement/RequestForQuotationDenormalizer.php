<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Procurement;

use App\ION\Resources\Procurement\RequestForQuotation;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RequestForQuotationDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'REQUEST_FOR_QUOTATION_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return RequestForQuotation::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        foreach ($data['lines'] as $key => $line) {
            $data['lines'][$key]['itemRevision'] = $line['site']['engineeringItemRevision'];
            $data['lines'][$key]['itemSignalCode'] = $line['site']['engineeringSignalCode'];
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
