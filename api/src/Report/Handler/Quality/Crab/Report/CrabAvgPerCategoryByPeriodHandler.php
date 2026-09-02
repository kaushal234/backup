<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\Report;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Repository\Quality\Crab\CrabRepository;
use Doctrine\ORM\EntityManagerInterface;

class CrabAvgPerCategoryByPeriodHandler
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;
    private readonly CrabRepository $crabRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        IriConverterInterface $iriConverter,
        CrabRepository $crabRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
        $this->crabRepository = $crabRepository;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Crab::class !== $resourceClass || 'reportAvgPerCategoryByPeriod' !== $x || !isset($options['from'], $options['to'], $options['factory'])) {
            return null;
        }

        $factory = $this->iriConverter->getResourceFromIri($options['factory']);
        if (!$factory instanceof Location) {
            return null;
        }

        $from = new \DateTime($options['from']);
        $to = new \DateTime($options['to']);

        // Query 1: count of CRABs per category per month
        $crabRows = $this->entityManager->createQueryBuilder()
            ->select([
                'c.category AS category',
                'SUBSTRING(er.firstGreenTagDate, 1, 7) AS month',
                'COUNT(c.id) AS crabCount',
            ])
            ->from(Crab::class, 'c')
            ->innerJoin('c.equipmentRecord', 'er')
            ->where('er.manufacturerLocation = :factory')
            ->andWhere('er.firstGreenTagDate >= :from')
            ->andWhere('er.firstGreenTagDate < :to')
            ->andWhere('c.category IS NOT NULL')
            ->andWhere("c.category != ''")
            ->setParameter('factory', $factory)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->groupBy('c.category')
            ->addGroupBy('month')
            ->getQuery()
            ->getScalarResult()
        ;

        // Query 2: count of GT units per month (denominator)
        $unitRows = $this->entityManager->createQueryBuilder()
            ->select([
                'SUBSTRING(er2.firstGreenTagDate, 1, 7) AS month',
                'COUNT(er2.id) AS unitCount',
            ])
            ->from(EquipmentRecord::class, 'er2')
            ->where('er2.manufacturerLocation = :factory')
            ->andWhere('er2.firstGreenTagDate >= :from')
            ->andWhere('er2.firstGreenTagDate < :to')
            ->setParameter('factory', $factory)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->groupBy('month')
            ->getQuery()
            ->getScalarResult()
        ;

        // Index unit counts by month for fast lookup
        $unitCountByMonth = [];
        foreach ($unitRows as $unitRow) {
            $unitCountByMonth[$unitRow['month']] = (int) $unitRow['unitCount'];
        }

        // Initialize all categories for all months at 0
        $categories = [
            Crab::QA,
            Crab::TEST,
            Crab::ASSY,
            Crab::PDI,
            Crab::PDI_SOL,
            Crab::PDI_CSC,
            Crab::PDI_INTERNAL,
        ];

        $period = new \DatePeriod(
            (clone $from)->modify('first day of this month'),
            new \DateInterval('P1M'),
            (clone $to)->modify('first day of next month'),
        );

        // Build rows with the expected x/y/value structure
        $filledRows = [];
        foreach ($period as $month) {
            foreach ($categories as $category) {
                $filledRows[$category][$month->format('Y-m')] = [
                    'x' => $month->format('Y-m'),
                    'y' => $category,
                    'value' => 0,
                ];
            }
        }

        // Fill with computed averages (crab count / GT unit count for that month)
        foreach ($crabRows as $row) {
            $month = $row['month'];
            $category = $row['category'];
            $unitCount = $unitCountByMonth[$month] ?? 0;

            if (!isset($filledRows[$category][$month])) {
                continue;
            }

            $filledRows[$category][$month]['value'] = $unitCount > 0
                ? round((int) $row['crabCount'] / $unitCount, 1)
                : 0;
        }

        $flatRows = array_merge(...array_values(array_map('array_values', $filledRows)));

        return new ReportDataProvider($flatRows);
    }
}
