<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\MIS;

use App\Entity\Module\Specification\UserStory;
use App\Serializer\Encoder\XlsxEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpecificationSpreadSheetNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UserStory && XlsxEncoder::FORMAT === $format;
    }

    /**
     * @param UserStory $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        return [
            '#ID' => $object->getId(),
            'category' => $object->category,
            'description' => $object->description,
            'roleAccesses' => $object->getRoleAccesses()->map(static function ($roleAccess) {
                return [
                    'id' => $roleAccess->getId(),
                    'locationProperty' => $roleAccess->locationProperty,
                    'group' => $roleAccess->group,
                ];
            })->toArray(),
        ];
    }
}
