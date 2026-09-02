<?php

declare(strict_types=1);

namespace App\Repository\Common;

use App\Entity\Common\Notification\Notification;
use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\Directory\People;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[]
     */
    public function findOlderThanAMonth()
    {
        $queryBuilder = $this
            ->createQueryBuilder('n')
            ->where('n.createdAt < :a_month_ago')
            ->setParameter('a_month_ago', new \DateTime('1 month ago'))
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function existsForRecipientReferenceAndTemplate(
        People $recipient,
        int $referenceId,
        NotificationTemplate $template
    ): bool {
        $qb = $this->createQueryBuilder('n');
        $qb
            ->select('COUNT(n.id)')
            ->where('n.people = :recipient')
            ->andWhere('n.referenceId = :referenceId')
            ->andWhere('n.template = :template')
            ->setParameter('recipient', $recipient)
            ->setParameter('referenceId', $referenceId)
            ->setParameter('template', $template);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
