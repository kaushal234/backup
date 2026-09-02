<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Directory;

use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use App\Entity\Directory\PremiseTag;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PremiseNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PREMISE_NORMALIZER_ALREADY_CALLED';

    private readonly PeopleRepository $peopleRepository;
    private readonly Security $security;

    public function __construct(PeopleRepository $peopleRepository, Security $security)
    {
        $this->peopleRepository = $peopleRepository;
        $this->security = $security;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Premise && null === ($context[self::ALREADY_CALLED] ?? null) && !\in_array('people:export', $context[AbstractNormalizer::GROUPS] ?? [], true);
    }

    /**
     * @param Premise $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $normalizedData['count'] = $this->peopleRepository->countPremiseUsers($object);

        $user = $this->security->getUser();

        if (!$user instanceof People) {
            return $normalizedData;
        }

        if ($user->getPremise() !== $object
            && !$this->security->isGranted('FEATURE_PREMISE_WRITE')
            && \in_array(PremiseTag::HOME_OFFICE_RESTRICTED, array_column($normalizedData['tags'] ?? [], 'name'), true)) {
            if ($normalizedData['address']['street1'] ?? null) {
                $normalizedData['address']['street1'] = '***';
            }
            if ($normalizedData['address']['street2'] ?? null) {
                $normalizedData['address']['street2'] = '***';
            }
            if ($normalizedData['address']['postalCode'] ?? null) {
                $normalizedData['address']['postalCode'] = '***';
            }
        }

        return $normalizedData;
    }
}
