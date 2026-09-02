<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class EntityOwnerFinder
{
    private readonly EntityManagerInterface $em;

    /**
     * OwnershipTransferManager constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return array|OwnerReflectionBag[]
     */
    public function getOwners(string $className): array
    {
        $owners = [];

        $allowedClasses = $this->getAllowedClasses($className);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        /** @var ClassMetadata $metadatum */
        foreach ($metadata as $metadatum) {
            foreach ($metadatum->getAssociationMappings() as $mapping) {
                // the target entity is not any in inheritance chain of source object
                if (!\in_array($mapping['targetEntity'], $allowedClasses, true)) {
                    continue;
                }

                // the property is not on the owning side of relation with source object
                if (!$mapping['isOwningSide']) {
                    continue;
                }

                $owners[] = new OwnerReflectionBag(
                    $metadatum->getReflectionClass(),
                    $metadatum->getPropertyAccessor($mapping['fieldName'])->getUnderlyingReflector()
                );
            }
        }

        return $owners;
    }

    private function getAllowedClasses(string $className): array
    {
        /** @var ClassMetadata $metadata */
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($className);

        return [...[$className], ...$metadata->parentClasses];
    }
}
