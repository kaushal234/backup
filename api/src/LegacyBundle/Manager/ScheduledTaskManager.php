<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use Doctrine\DBAL\Connection;

class ScheduledTaskManager
{
    public function __construct(private readonly Connection $legacyConnection)
    {
    }

    public function findActiveScheduledTasksByEndDate(\DateTimeInterface $endDate): array
    {
        $qb = $this->legacyConnection->createQueryBuilder();

        $qb
            ->select('st.id', 'st.assignor', 'st.description', 'st.date_end')
            ->from('cal_st', 'st')
            ->where('st.date_end = :endDate')
            ->andWhere('st.status = :status')
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->setParameter('status', 'ACTIVE')
        ;

        return $this->legacyConnection
            ->executeQuery($qb->getSQL(), $qb->getParameters())
            ->fetchAllAssociative();
    }
}
