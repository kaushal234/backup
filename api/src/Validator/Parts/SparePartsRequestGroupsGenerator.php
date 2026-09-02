<?php

declare(strict_types=1);

namespace App\Validator\Parts;

use ApiPlatform\Symfony\Validator\ValidationGroupsGeneratorInterface;
use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\SparePartsRequestDeliveryAddress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;

class SparePartsRequestGroupsGenerator implements ValidationGroupsGeneratorInterface
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param SparePartsRequest $object
     */
    public function __invoke($object): array
    {
        if ($this->entityManager->contains($object->getDeliveryAddress())) {
            return [Constraint::DEFAULT_GROUP];
        }

        return [Constraint::DEFAULT_GROUP, SparePartsRequestDeliveryAddress::COMPANY_VALIDATION_GROUP];
    }
}
