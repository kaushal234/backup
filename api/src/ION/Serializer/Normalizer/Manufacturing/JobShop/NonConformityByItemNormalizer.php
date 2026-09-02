<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\Manufacturing\JobShop;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NonConformityByItemNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    // Used on support to not normalized the same object twice
    private array $cachedItems = [];

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            ($data instanceof NonConformityByItemInterface)
            && \array_key_exists(NonConformityByItemContextNormalizer::ITEM_LIST, $context)
            && !\in_array($data->getPartNumber(), $this->cachedItems, true)
        ;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $this->cachedItems[] = $object->getPartNumber();
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $normalizedData['numberNonConformity'] = $context[NonConformityByItemContextNormalizer::ITEM_LIST][$object->getPartNumber()] ?? 0;

        return $normalizedData;
    }
}
