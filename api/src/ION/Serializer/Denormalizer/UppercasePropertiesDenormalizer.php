<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class UppercasePropertiesDenormalizer implements DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /** @var string */
    final public const SERIALIZER_FORCE_LOWERCASE = '_serializer_force_lowercase';
    /** @var string */
    private const ALREADY_CALLED = 'UPPERCASE_PROPERTIES_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_array($data) && true === ($context[self::SERIALIZER_FORCE_LOWERCASE] ?? false) && false === ($context[self::ALREADY_CALLED][$type.'-'.serialize($data)] ?? false);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = ($context[self::ALREADY_CALLED] ?? []) + [$type.'-'.serialize($data) => true];

        foreach ($data as $key => $value) {
            if (!\is_string($key)) {
                continue;
            }
            unset($data[$key]);
            $data[lcfirst($key)] = $value;
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
