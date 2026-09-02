<?php

declare(strict_types=1);

namespace App\Repository\Quality;

use App\Entity\Quality\CalibratedTools\Tool;
use App\Entity\Quality\CalibratedTools\ToolType;
use App\Entity\Quality\LocationArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ToolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tool::class);
    }

    /**
     * @return array|Tool[]
     */
    public function getActiveAndCalibrationDueSoonTools(): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->where('t.status IN (:statuses)')
            ->setParameter('statuses', [Tool::ACTIVE, Tool::CALIBRATION_DUE_SOON]);

        return $qb->getQuery()->getResult();
    }

    public function getIdentifiersForLocationArea(LocationArea $locationArea): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->select('t.id')
            ->where('t.locationArea = :locationArea')
            ->setParameter('locationArea', $locationArea)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForToolType(ToolType $toolType): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->select('t.id')
            ->where('t.toolType = :toolType')
            ->setParameter('toolType', $toolType)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
