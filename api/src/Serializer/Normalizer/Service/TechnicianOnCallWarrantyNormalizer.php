<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Service;

use ApiPlatform\Metadata\Get;
use App\Entity\Service\TechnicianOnCall;
use LegacyBundle\Manager\WarrantyClaimManager;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TechnicianOnCallWarrantyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'TECHNICIAN_ON_CALL_WARRANTY_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly WarrantyClaimManager $warrantyClaimManager
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TechnicianOnCall && ($context['operation'] ?? null) instanceof Get && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param TechnicianOnCall $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        $normalizedTechnicianOnCall = $this->normalizer->normalize($object, $format, $context);
        $normalizedTechnicianOnCall['warrantyInformation'] = $object->equipmentRecord ? $this->warrantyClaimManager->getWarrantyStatus($object->equipmentRecord) : '---';

        return $normalizedTechnicianOnCall;
    }
}
