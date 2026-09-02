<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Activity\Activity;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActivityPositionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ACTIVITY_POSITION_NORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (null !== ($context[self::ALREADY_CALLED] ?? null)) {
            return false;
        }

        if (!$data) {
            return false;
        }

        if (!\is_array($data)) {
            return false;
        }

        if (!($context[AbstractNormalizer::GROUPS] ?? []) || !\in_array('activity_position', $context[AbstractNormalizer::GROUPS], true)) {
            return false;
        }

        $firstComment = reset($data);

        return $firstComment instanceof Activity;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        $normalizeData = $this->normalizer->normalize($object, $format, $context);
        $totalItems = $normalizeData['hydra:totalItems'];

        if (0 === $totalItems) {
            return $normalizeData;
        }

        foreach ($normalizeData['hydra:member'] as &$element) {
            $element['position'] = 1 + \count(array_filter($normalizeData['hydra:member'], static function ($other) use ($element) {
                return strtotime($element['createdAt']) > strtotime($other['createdAt'])
                || (strtotime($element['createdAt']) === strtotime($other['createdAt']) && $element['id'] < $other['id']);
            }));
        }

        return $normalizeData;
    }
}
