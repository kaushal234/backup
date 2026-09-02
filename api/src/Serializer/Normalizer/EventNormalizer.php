<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\HumanResources\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'EVENT_NORMALIZER_ALREADY_CALLED';
    private readonly EntityManagerInterface $entityManager;
    private readonly IriConverterInterface $iriConverter;
    private array $rates = [];

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Event && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param Event $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $locationRepository = $this->entityManager->getRepository(Location::class);
        foreach ($locationRepository->findBy(['address.country' => $object->country->getIsoCode2()]) as $location) {
            $normalizedData['concernedLocations'][] = $location->getName();
        }

        return $normalizedData;
    }
}
