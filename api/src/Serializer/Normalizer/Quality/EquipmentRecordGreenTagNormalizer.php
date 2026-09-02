<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Quality;

use App\Entity\EquipmentRecord;
use LegacyBundle\Manager\EquipmentRecordManager;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EquipmentRecordGreenTagNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'EQUIPMENT_RECORD_GREEN_TAG_NORMALIZER_ALREADY_CALLED';
    private readonly EquipmentRecordManager $manager;

    public function __construct(EquipmentRecordManager $manager)
    {
        $this->manager = $manager;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EquipmentRecord && null === ($context[self::ALREADY_CALLED] ?? null) && \array_key_exists(AbstractNormalizer::GROUPS, $context) && \in_array('equipment_record_green_tag', $context[AbstractNormalizer::GROUPS], true);
    }

    /**
     * @param EquipmentRecord $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $equipmentRecord = $object->getLegacyId();
        $equipmentRecordLegacy = $this->manager->findByLegacyId($equipmentRecord);
        $normalizedData['isGreenTag'] = null !== $equipmentRecordLegacy['dgt_act'] && $equipmentRecordLegacy['dgt_act'] > $equipmentRecordLegacy['dyt'];

        return $normalizedData;
    }
}
