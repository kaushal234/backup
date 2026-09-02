<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Quality;

use App\Entity\Quality\NonConformity;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NonConformityNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'NON_CONFORMITY_NORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof NonConformity && \in_array('non_conformity:legacy', $context[AbstractNormalizer::GROUPS], true) && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param NonConformity $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $normalizedData['reporterId'] = null !== $object->reportedBy ? $object->reportedBy->getId() : '';
        $normalizedData['reportedByFullName'] = null !== $object->reportedBy ? $object->reportedBy->getDisplayName() : '';
        $normalizedData['currencyName'] = null !== $object->currency ? $object->currency->getName() : '';

        return $normalizedData;
    }
}
