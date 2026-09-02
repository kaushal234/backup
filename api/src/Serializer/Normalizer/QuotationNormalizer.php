<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\SPQ\Quotation;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QuotationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'QUOTATION_NORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Quotation && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param Quotation $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if ('csv' === $format) {
            // This will ensure that there will not be any missing columns
            if (!($normalizedData['source'] ?? []) && \array_key_exists('source', $context[AbstractNormalizer::ATTRIBUTES] ?? [])) {
                $normalizedData['source'] = ['lastname' => null, 'firstname' => null];
            }
            if (!($normalizedData['quoter'] ?? []) && \array_key_exists('quoter', $context[AbstractNormalizer::ATTRIBUTES] ?? [])) {
                $normalizedData['quoter'] = ['lastname' => null, 'firstname' => null];
            }
        }

        return $normalizedData;
    }
}
