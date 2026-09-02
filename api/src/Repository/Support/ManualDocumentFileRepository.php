<?php

declare(strict_types=1);

namespace App\Repository\Support;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ManualDocumentFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ManualDocumentFile::class);
    }

    public function getManualSectionFiles(Manual $manual): array
    {
        $qb = $this->createQueryBuilder('f');

        return $qb
            ->leftJoin('f.manualDocument', 'md')
            ->where('md.manual = :manualId')
            ->andWhere('md.type = :manualDocumentType')
            ->setParameter(':manualId', $manual->getId())
            ->setParameter(':manualDocumentType', ManualDocument::MANUAL_SECTION)
            ->getQuery()
            ->getResult()
        ;
    }
}
