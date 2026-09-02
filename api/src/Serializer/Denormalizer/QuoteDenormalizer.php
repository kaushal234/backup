<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\Sales\Quote;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class QuoteDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'QUOTE_ALREADY_CALLED';

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;
        $xml = $this->denormalizer->denormalize($data['xml']->getContent(), 'string', 'xml');
        $xmlElement = new \SimpleXMLElement($xml);

        $data['quoteNumber'] = (string) $xmlElement->DataArea->Quotation->QuotationHeader->Equote;
        $data['xml'] = $xml;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Quote::class === $type && !($context[self::ALREADY_CALLED] ?? null) && 'create_quote' === ($context['operation_name'] ?? null);
    }
}
