<?php

declare(strict_types=1);

namespace App\Identifier\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\TypeInfo\TypeIdentifier;

final class StringDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, ?string $format = null, array $context = []): string
    {
        return urldecode((string) $data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return TypeIdentifier::STRING->value === $type && \is_string($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [TypeIdentifier::STRING->value => false];
    }
}
