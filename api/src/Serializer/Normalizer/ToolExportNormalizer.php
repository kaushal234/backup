<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Quality\CalibratedTools\Tool;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ToolExportNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TOOL_CSV_NORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Tool && null === ($context[self::ALREADY_CALLED] ?? null) && CsvEncoder::FORMAT === $format;
    }

    /**
     * @param Tool $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        unset($normalizedData['statusUpdatedAt']);

        $normalizedData['type'] = $object->getToolType()->getDescription();
        $normalizedData['area'] = $object->getLocationArea()->getName();
        $normalizedData['supervisor'] = $object->getLocationArea()->getSupervisor()->getUserIdentifier();
        $normalizedData['statusUpdatedAt'] = (null !== $statusUpdatedAt = $object->getStatusUpdatedAt()) ? $statusUpdatedAt->format('Y-m-d H:i:s') : '';

        return $normalizedData;
    }
}
