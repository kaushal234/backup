<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Purchasing\SupplierRanking\FileCategory;
use App\Entity\Purchasing\SupplierRanking\SupplierRankingFile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FileCategoryDeletionVoter implements DeletionVoterInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof FileCategory;
    }

    /**
     * {@inheritdoc}
     *
     * @param FileCategory $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        /** @var EntityManagerInterface $em */
        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $files = $em->getRepository(SupplierRankingFile::class)->findBy(['category' => $entity]);
        if ([] !== $files) {
            return (new RejectedDeletionReason())
                ->setType('category')
                ->setLabel($entity->name)
            ;
        }

        return null;
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
