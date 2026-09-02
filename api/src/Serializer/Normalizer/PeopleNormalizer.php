<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Directory\PositionClassification;
use App\Entity\Group;
use App\Repository\AclRepository;
use App\Repository\Directory\PositionClassificationRepository;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Contracts\Service\Attribute\Required;

class PeopleNormalizer extends UserNormalizer
{
    /**
     * @var string
     */
    private const ALREADY_CALLED_AT_LEVEL = 'PEOPLE_NORMALIZER_ALREADY_CALLED';

    private AclRepository $repository;

    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($tokenStorage);
    }

    #[Required]
    public function setRepository(AclRepository $repository)
    {
        $this->repository = $repository;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof People && 'jsonld' === $format && (null === ($context[self::ALREADY_CALLED_AT_LEVEL] ?? null) || ($context[self::ALREADY_CALLED_AT_LEVEL] < ($context['api_sub_level'] ?? 0)));
    }

    /**
     * @param People $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED_AT_LEVEL] = $context['api_sub_level'] ?? 0;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (!($context[AbstractNormalizer::GROUPS] ?? []) || \in_array('people_list', $context[AbstractNormalizer::GROUPS], true)) {
            return $normalizedData;
        }

        /** @var AclRepository $aclRepository */
        $aclRepository = $this->entityManager->getRepository(Acl::class);

        if (\in_array(PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, $context[AbstractNormalizer::GROUPS], true)) {
            $normalizedData['roles'] = $this->extractRoles($object, $context);
            $normalizedData['acls'] = $aclRepository->getUserGroupsForIntranet($object);
        }

        if (\in_array('people_public', $context[AbstractNormalizer::GROUPS], true)) {
            unset($normalizedData['passwordUpdatedAt'], $normalizedData['passwordExpirationDate']);
        }

        if (\in_array('people_detail', $context[AbstractNormalizer::GROUPS], true)
            && null !== $object->getBusinessUnit()
            && null !== $object->getPosition()
        ) {
            /** @var PositionClassificationRepository $positionClassificationRepository */
            $positionClassificationRepository = $this->entityManager->getRepository(PositionClassification::class);
            $positionClassification = $positionClassificationRepository->getPositionClassificationForPeople($object);

            if (null !== $positionClassification) {
                $normalizedData['positionCategory'] = $positionClassification->positionCategory->name;
            }
        }

        if (\in_array('user:me', $context[AbstractNormalizer::GROUPS], true)) {
            $erp = null;
            $timeZone = null;
            $locationCapabilities = [
                'sso' => false,
                'factory' => false,
                'warehouse' => false,
                'sparePartsHub' => false,
                'serviceHub' => false,
                'headQuarter' => false,
            ];
            if (null !== $businessUnit = $object->getBusinessUnit()) {
                $location = $businessUnit->getLocation();
                $erp = $location->getErp();
                $capability = $location->getCapability();
                $locationCapabilities = [
                    'sso' => $capability->isSso(),
                    'factory' => $capability->isFactory(),
                    'warehouse' => $capability->isWarehouse(),
                    'sparePartsHub' => $capability->isSparePartsHub(),
                    'serviceHub' => $capability->isServiceHub(),
                    'headQuarter' => $capability->isHeadQuarter(),
                ];
                $timeZone = $location->getTimeZone();
            }

            $normalizedData += [
                'erp' => $erp,
                'locationCapabilities' => $locationCapabilities,
                'timeZone' => $timeZone,
            ];
        }

        if (\in_array('group_member', $context[AbstractNormalizer::GROUPS], true)) {
            foreach ($normalizedData['acls'] as &$acl) {
                $acl['standard'] = false;
                if (null !== $normalizedData['position']) {
                    /** @var Group $standardGroup */
                    foreach ($object->getGroupsForDivision() as $standardGroup) {
                        if ($standardGroup->getName() === $acl['group']['name']
                            || 'ACL_AUTH_INTRANET' === $acl['group']['name']
                            || 'ACL_AUTH_JAVELO' === $acl['group']['name']
                            || 'ACL_AUTH_AGILE' === $acl['group']['name']) {
                            $acl['standard'] = true;
                            continue 2;
                        }
                    }
                }
            }
        }

        return $normalizedData;
    }
}
