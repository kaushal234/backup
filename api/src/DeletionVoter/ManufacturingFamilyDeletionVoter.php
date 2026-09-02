<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Manufacturing\ManufacturingFamily;
use App\Entity\Sales\Product;
use Doctrine\ORM\EntityManagerInterface;

class ManufacturingFamilyDeletionVoter implements DeletionVoterInterface
{
    public EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports($entity): bool
    {
        return $entity instanceof ManufacturingFamily;
    }

    /**
     * {@inheritdoc}
     *
     * @param ManufacturingFamily $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('Manufacturing Family')->setLabel($entity->getName());
        $products = $this->entityManager->getRepository(Product::class)->findBy(['manufacturingFamily' => $entity->getId()]);
        if ([] !== $products) {
            $names = array_map(static fn (Product $product) => $product->getName(), $products);

            return $reason
                ->setIdentifiers($names)
                ->setCountedType('product')
            ;
        }

        return null;
    }
}
