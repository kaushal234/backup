<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Service;

use ApiPlatform\Metadata\Get;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Entity\ServiceBulletin;
use LegacyBundle\Repository\ServiceBulletinRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TechnicianOnCallNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'TECHNICIAN_ON_CALL_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly EntityManagerInterface $legacyEntityManager
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

        /** @var ServiceBulletinRepository $repository */
        $repository = $this->legacyEntityManager->getRepository(ServiceBulletin::class);
        $serviceBulletins = null !== $object->equipmentRecord ? $repository->findCompulsoryByEquipmentRecordId($object->equipmentRecord->getLegacyId()) : null;

        $normalizedServiceBulletins = null !== $serviceBulletins ? $this->normalizer->normalize($serviceBulletins, null, ['groups' => ['legacy:service_bulletin:detail']]) : null;

        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $normalizedData['serviceBulletins'] = $normalizedServiceBulletins;

        return $normalizedData;
    }
}
