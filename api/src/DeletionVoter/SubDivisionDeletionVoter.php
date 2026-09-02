<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Entity\Sales\AbstractSalesRepresentative;
use App\Entity\Sales\MainSalesRepresentative;
use App\Entity\Sales\SecondarySalesRepresentative;
use Doctrine\ORM\EntityManagerInterface;

class SubDivisionDeletionVoter implements DeletionVoterInterface
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof SubDivision;
    }

    /**
     * {@inheritdoc}
     *
     * @param SubDivision $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('sub division')->setLabel($entity->name);

        if (!$entity->getRegions()->isEmpty()) {
            $ids = array_map(static fn (Region $businessUnit) => ['id' => $businessUnit->getId()], $entity->getRegions()->toArray());

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('Region')
            ;
        }

        $salesRepresentativeRepository = $this->entityManager->getRepository(AbstractSalesRepresentative::class);
        if (!empty($salesRepresentatives = $salesRepresentativeRepository->findBy(['subDivision' => $entity]))) {
            $customers = [];
            /** @var MainSalesRepresentative|SecondarySalesRepresentative $salesRepresentative */
            foreach ($salesRepresentatives as $salesRepresentative) {
                $customers[] = $salesRepresentative->customer->getId();
            }

            return $reason
                ->setIdentifiers(array_unique($customers))
                ->setCountedType('Customer')
            ;
        }

        return null;
    }
}
