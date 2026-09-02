<?php

declare(strict_types=1);

namespace App\Repository\Service;

use App\Entity\EquipmentRecord;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use App\Util\IriToId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TechnicianOnCallRepository extends ServiceEntityRepository
{
    public const DELAY_LESS_THAN_7 = 'less_than_7';
    public const DELAY_BETWEEN_7_AND_14 = 'between_7_and_14';
    public const DELAY_BETWEEN_14_AND_21 = 'between_14_and_21';
    public const DELAY_MORE_THAN_21 = 'more_than_21';

    public const DELAY_CATEGORIES = [
        self::DELAY_LESS_THAN_7,
        self::DELAY_BETWEEN_7_AND_14,
        self::DELAY_BETWEEN_14_AND_21,
        self::DELAY_MORE_THAN_21,
    ];

    public function __construct(
        ManagerRegistry $registry,
        private readonly IriToId $iriToId,
    ) {
        parent::__construct($registry, TechnicianOnCall::class);
    }

    /**
     * @param list<string>|null $serviceActivityNames filter by ServiceActivity names (e.g. [ServiceActivity::TROUBLESHOOTING]); null means no filter
     */
    public function countByEquipmentRecordAndCreatedDate(EquipmentRecord $equipmentRecord, \DateTimeInterface $createdSince, ?array $serviceActivityNames = null): mixed
    {
        $queryBuilder = $this->createQueryBuilder('t');

        $queryBuilder
            ->select('COUNT(t)')
            ->andWhere('t.equipmentRecord = :equipmentRecord')
            ->andWhere($queryBuilder->expr()->gte('t.createdAt', ':createdSince'))
            ->setParameter('equipmentRecord', $equipmentRecord)
            ->setParameter('createdSince', $createdSince)
        ;

        if (null !== $serviceActivityNames) {
            $queryBuilder
                ->join('t.serviceActivity', 'sa')
                ->andWhere('sa.name IN (:serviceActivityNames)')
                ->setParameter('serviceActivityNames', $serviceActivityNames)
            ;
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function getResolutionDelaysStats(\DateTimeInterface $start, \DateTimeInterface $end, ?string $salesServiceOrganisation = null, bool $withRemote = false): array
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder
            ->select(\sprintf("
                CASE
                    WHEN DATE_DIFF(t.solvedAt, t.createdAt) < 7 THEN '%s'
                    WHEN DATE_DIFF(t.solvedAt, t.createdAt) < 14 THEN '%s'
                    WHEN DATE_DIFF(t.solvedAt, t.createdAt) < 21 THEN '%s'
                    ELSE '%s'
                END AS delay",
                self::DELAY_LESS_THAN_7,
                self::DELAY_BETWEEN_7_AND_14,
                self::DELAY_BETWEEN_14_AND_21,
                self::DELAY_MORE_THAN_21
            ))
            ->addSelect('COUNT(t.id) AS total')
            ->join('t.technicianOnCallType', 'tt')
            ->join('t.serviceActivity', 'sa')
            ->where('t.solvedAt >= :start')
            ->andWhere('t.solvedAt < :end')
            ->andWhere('tt.name != :excludedType')
            ->andWhere($queryBuilder->expr()->in('sa.name', ':serviceActivity'))
            ->groupBy('delay')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('excludedType', 'toc.type.customer')
            ->setParameter('serviceActivity', ['Troubleshooting', 'Info request'])
        ;

        if ($withRemote) {
            $queryBuilder
                ->addSelect('SUM(CASE WHEN csr.id IS NULL THEN 1 ELSE 0 END) AS without_csr')
                ->leftJoin('t.customerServiceRecords', 'csr')
            ;
        }

        if ($salesServiceOrganisation) {
            $queryBuilder
                ->andWhere('t.salesOrganisationService = :sso_id')
                ->setParameter('sso_id', $this->iriToId->getId($salesServiceOrganisation))
            ;
        }

        $results = $queryBuilder->getQuery()->getResult();

        $indexedResults = [];
        foreach ($results as $row) {
            $indexedResults[$row['delay']] = $row;
        }

        foreach (self::DELAY_CATEGORIES as $delay) {
            if (isset($indexedResults[$delay])) {
                continue;
            }

            $indexedResults[$delay] = [
                'delay' => $delay,
                'total' => 0,
            ];

            if ($withRemote) {
                $indexedResults[$delay]['without_csr'] = 0;
            }
        }

        $sortResults = [];
        foreach (self::DELAY_CATEGORIES as $delay) {
            $sortResults[] = $indexedResults[$delay];
        }

        return $sortResults;
    }

    public function countResolved(\DateTimeInterface $start, \DateTimeInterface $end, ?string $salesServiceOrganisation = null): int
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder
            ->select('COUNT(t.id)')
            ->join('t.technicianOnCallType', 'tt')
            ->join('t.serviceActivity', 'sa')
            ->where('t.solvedAt >= :start')
            ->andWhere('t.solvedAt < :end')
            ->andWhere('tt.name != :excludedType')
            ->andWhere($queryBuilder->expr()->in('sa.name', ':serviceActivity'))
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('excludedType', 'toc.type.customer')
            ->setParameter('serviceActivity', ['Troubleshooting', 'Info request'])
        ;

        if ($salesServiceOrganisation) {
            $queryBuilder
                ->andWhere('t.salesOrganisationService = :sso_id')
                ->setParameter('sso_id', $this->iriToId->getId($salesServiceOrganisation))
            ;
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
