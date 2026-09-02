<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Parts;

use App\Entity\Parts\SparePartsRequest;
use App\Manager\Parts\SparePartsRequestManager;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SparePartsRequestPartTrackingNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const NORMALIZATION_GROUP_NAME = 'tracking';

    private const ALREADY_CALLED = 'SPR_PART_TRACKING_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly SparePartsRequestManager $sparePartsRequestManager,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return \in_array(self::NORMALIZATION_GROUP_NAME, $context[AbstractNormalizer::GROUPS] ?? [], true) && $data instanceof SparePartsRequest && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param SparePartsRequest $data
     *
     * @throws ExceptionInterface
     */
    public function normalize($data, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        $this->sparePartsRequestManager->setShipments($data);

        return $this->normalizer->normalize($data, $format, $context);
    }
}
