<?php

declare(strict_types=1);

namespace App\Report\Handler\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Sales\ProductFamily;
use App\Entity\Support\EquipmentRecord\EstimatedGreenTagQuantityReport;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentRecordGreenTagDateHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection,
        private readonly IriConverterInterface $iriConverter
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (EstimatedGreenTagQuantityReport::class !== $resourceClass || 'ratio' !== $y || 'month' !== $x) {
            return null;
        }

        $month = $options['month'] ?? (new \DateTime())->format('Y-m');
        $startOfMonth = new \DateTimeImmutable("$month-01 00:00:00");
        $endOfMonth = $startOfMonth->modify('last day of this month 23:59:59');

        $start = $options['start'] ?? $startOfMonth->format('Y-m-d');
        $end = $options['end'] ?? $endOfMonth->format('Y-m-d');

        $factoryCondition = '';
        if (isset($options['factory'])) {
            $factoryCondition = ' AND dl.id = :factory ';
        }

        $familiesCondition = '';
        $familyIds = [];
        if (!empty($options['families'])) {
            foreach ($options['families'] as $familyIri) {
                $family = $this->iriConverter->getResourceFromIri($familyIri);
                if (!$family instanceof ProductFamily) {
                    return null;
                }
                $familyIds[] = $family->getId();
            }

            if (!empty($familyIds)) {
                $familiesCondition = \sprintf(' AND f.id IN (%s) ', implode(',', $familyIds));
            }
        }

        $sql = <<<SQL
                WITH RECURSIVE calendar AS (
                SELECT DATE(:start) AS d
                UNION ALL
                SELECT d + INTERVAL 1 DAY
                FROM calendar
                WHERE d < DATE(:end)
            ),
            weekdays AS (
                SELECT
                    d,
                    DATE_FORMAT(d, '%Y-%m') AS month
                FROM calendar
                WHERE DAYOFWEEK(d) BETWEEN 2 AND 6
            ),
            working_days_by_month_location AS (
                SELECT
                    w.month,
                    dl.id AS location_id,
                    COUNT(*) 
                      - COUNT(DISTINCT CASE WHEN e.id IS NOT NULL THEN w.d END) AS working_days
                FROM weekdays w
                INNER JOIN directory_location dl ON 1=1
                LEFT JOIN countries c
                  ON c.iso_code_2 = dl.address_country
                LEFT JOIN events e
                  ON e.day_off = TRUE
                 AND e.country_id = c.id
                 AND w.d BETWEEN DATE(e.started_at) AND DATE(e.ended_at)
                GROUP BY w.month, dl.id
            ),
            daily AS (
                SELECT
                    DATE(COALESCE(er.first_green_tag_date, er.yellow_tag_date)) AS day,
                    dl.name,
                    dl.id AS location_id,
                    GROUP_CONCAT(er.serial_number) AS serial_numbers,
                    COUNT(er.id) AS gt
                FROM equipment_records er
                INNER JOIN directory_location dl
                    ON dl.id = er.manufacturer_location_id
                INNER JOIN products p 
                    ON p.id = er.product_id
                INNER JOIN product_families f 
                    ON f.id = p.family_id
                WHERE (er.combination_mode <> 'PRE-ASSEMBLY' OR er.combination_mode IS NULL)
                  AND er.light = 0
                  AND (
                        (er.first_green_tag_date BETWEEN :start AND :end)
                     OR (er.first_green_tag_date IS NULL AND er.yellow_tag_date BETWEEN :start AND :end)
                  )
                  $factoryCondition
                  $familiesCondition
                GROUP BY day, dl.id
            ),
            daily_counts AS (
                SELECT
                    DATE_FORMAT(report.day, '%Y-%m') AS month,
                    dl.id AS location_id,
                    COUNT(er_map.equipment_record_id) AS daily_count
                FROM estimated_green_tag_quantity_report AS report
                INNER JOIN directory_location dl
                    ON dl.id = report.manufacturer_location_id
                INNER JOIN estimated_green_tag_quantity_reports_equipment_records er_map
                    ON er_map.estimated_green_tag_quantity_report_id = report.id
                INNER JOIN equipment_records er_egt
                    ON er_map.equipment_record_id = er_egt.id
                INNER JOIN products p 
                    ON p.id = er_egt.product_id
                INNER JOIN product_families f 
                    ON f.id = p.family_id
                WHERE report.day >= :start
                  AND report.day <= :end
                  AND (er_egt.combination_mode <> 'PRE-ASSEMBLY' OR er_egt.combination_mode IS NULL)
                  AND er_egt.light = 0
                  $factoryCondition
                  $familiesCondition
                GROUP BY report.day, dl.id
            ),
            monthly AS (
                SELECT
                    dc.month,
                    dc.location_id,
                    SUM(dc.daily_count) / NULLIF(wd.working_days, 0) AS monthly_ratio,
                    wd.working_days
                FROM daily_counts dc
                INNER JOIN working_days_by_month_location wd
                  ON wd.month = dc.month
                 AND wd.location_id = dc.location_id
                GROUP BY dc.month, dc.location_id, wd.working_days
            )
            SELECT
                cal.d AS x,
                dl.name AS y,
                m.monthly_ratio AS ratio,
                d.serial_numbers,
                CASE
                    WHEN m.monthly_ratio < 3 THEN GREATEST(FLOOR(m.monthly_ratio), 1)
                    WHEN m.monthly_ratio >= 3 AND m.monthly_ratio < 5 THEN FLOOR(m.monthly_ratio)
                    ELSE FLOOR(m.monthly_ratio) - 1
                END AS min,
                CASE
                    WHEN m.monthly_ratio < 3 THEN FLOOR(m.monthly_ratio) + 1
                    WHEN m.monthly_ratio >= 3 AND m.monthly_ratio < 5 THEN FLOOR(m.monthly_ratio) + 2
                    ELSE FLOOR(m.monthly_ratio) + 2
                END AS max,
                COALESCE(d.gt, 0) AS value,
                m.working_days AS working_days
            FROM calendar cal
            JOIN monthly m
              ON m.month = DATE_FORMAT(cal.d, '%Y-%m')
            INNER JOIN directory_location dl
              ON dl.id = m.location_id
            LEFT JOIN daily d
              ON d.day = cal.d
             AND d.location_id = m.location_id
            ORDER BY cal.d ASC, dl.id
            ;
            SQL;

        $params = [
            'start' => $start,
            'end' => $end,
        ];

        if (isset($options['factory'])) {
            /** @var Location $location */
            $location = $this->iriConverter->getResourceFromIri($options['factory']);
            $params['factory'] = $location->getId();
        }

        if (!empty($familyIds)) {
            $params['families'] = $familyIds;
        }

        $results = $this->entityManager
            ->getConnection()
            ->executeQuery($sql, $params)
            ->fetchAllAssociative();

        foreach ($results as &$result) {
            $result['extraData'] = [
                'ratio' => $result['ratio'],
                'min' => $result['min'],
                'max' => $result['max'],
                'serialNumbers' => $result['serial_numbers'],
                'workingDays' => $result['working_days'],
            ];
        }

        return new ReportDataProvider($results);
    }
}
