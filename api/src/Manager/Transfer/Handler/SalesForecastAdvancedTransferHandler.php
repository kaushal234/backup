<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\EntityRepository;

class SalesForecastAdvancedTransferHandler extends AbstractTransferHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle($source, $target, OwnerReflectionBag $relation, array $conditions = []): void
    {
        if (null !== $source || !isset($conditions['sso']) || !$this->supports($relation->getReflectionClass()->getName(), $relation->getReflectionProperty())) {
            return;
        }

        /** @var EntityRepository $repo */
        $repo = $this->em->getRepository($relation->getReflectionClass()->getName());
        $qb = $repo->createQueryBuilder('o');

        $qb
            ->andWhere('o.status IN (:allowedStatuses)')
            ->andWhere('o.sso = :sso')
            ->setParameter('sso', $conditions['sso'])
            ->setParameter('allowedStatuses', SalesForecast::OPEN_STATUSES)
        ;

        if (isset($conditions['country'])) {
            $qb->andWhere('o.country = :country')->setParameter('country', $conditions['country']);
        }

        if (isset($conditions['asmSource'])) {
            $qb->andWhere('o.asm = :asmSource')->setParameter('asmSource', $conditions['asmSource']);
        }

        if (isset($conditions['buyer'])) {
            $qb->andWhere('o.buyer = :buyer')->setParameter('buyer', $conditions['buyer']);
        }

        if (isset($conditions['endUser'])) {
            $qb->andWhere('o.endUser = :endUser')->setParameter('endUser', $conditions['endUser']);
        }

        $this->update($qb, $relation->getReflectionProperty()->getName(), $target);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'handler.sfr.asm.advanced';
    }

    /**
     * {@inheritdoc}
     */
    private function supports(string $className, \ReflectionProperty $property): bool
    {
        return SalesForecast::class === $className && 'asm' === $property->getName();
    }
}
