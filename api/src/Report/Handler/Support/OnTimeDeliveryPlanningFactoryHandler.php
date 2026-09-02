<?php

declare(strict_types=1);

namespace App\Report\Handler\Support;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\Filter\Support\EquipmentRecord\EquipmentRecordOdpFilter;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class OnTimeDeliveryPlanningFactoryHandler implements ReportHandlerInterface
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

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (EquipmentRecord::class !== $resourceClass || 'dashboard' !== $y || 'factory' !== $x) {
            return null;
        }

        $sql = "
SELECT
    l.name AS locationName,
    l.id AS locationId,
    COUNT((o.id is not NULL) AND er.date_shipped is NULL OR NULL) as Backlog,
    COUNT(DATEDIFF(NOW(), er.green_tag_date) between 0 and 14 AND er.date_shipped is NULL OR NULL) as 'Number Green Tagged in previous 2 Weeks',
    COUNT(DATEDIFF(shipping_line.estimated_pick_up_date, NOW()) between 0 AND 14 OR NULL) as 'Number Shipping within next 2 weeks',
    COUNT(DATEDIFF(er.estimated_green_tag_date, NOW()) between 0 AND 14 AND er.date_shipped is NULL AND er.green_tag_date is NULL OR NULL) as 'Number Green Tagging within next 2 weeks',
    COUNT(DATEDIFF(NOW(), f.factory_promised_delivery_date) > 0 AND er.green_tag_date is NULL AND er.date_shipped is NULL OR NULL) as 'Number Late',
    COUNT(DATEDIFF(NOW(), er.date_shipped) between 0 and 14 OR NULL) as 'Number Shipped in previous 2 weeks',
    COUNT((DATEDIFF(er.green_tag_date, er.yellow_tag_date) > 0 OR DATEDIFF(er.green_tag_date, er.yellow_tag_date) IS NULL) AND er.green_tag_date is not NULL AND er.date_shipped is NULL OR NULL) AS 'GT Not Shipped (GTNS)',
    COUNT((er.yellow_tag_date >= er.green_tag_date OR er.green_tag_date IS NULL) AND er.yellow_tag_date is not NULL AND er.date_shipped is NULL OR NULL) AS 'YT Not Shipped (YTNS)',
    (
        SELECT COUNT(distinct er2.id)
        FROM equipment_records er2
            LEFT JOIN customers c ON er2.buyer_id = c.id
        WHERE c.name IN ('**AVAILABLE FOR SALE**', 'TLD EUROPE', 'TLD AMERICA', 'TLD ASIA')
            AND er2.date_shipped is NULL
            AND er2.manufacturer_location_id = er.manufacturer_location_id
            AND c.deleted_at IS NULL
    ) AS 'Available For Sale (AFS)'
FROM equipment_records er
    LEFT JOIN sales_order_factory f ON f.equipment_record_id = er.id
    LEFT JOIN directory_location l ON er.manufacturer_location_id = l.id
    LEFT JOIN sales_order_lines line ON f.order_line_id = line.id
    LEFT JOIN sales_orders o ON o.id = line.order_id
    LEFT JOIN (
        SELECT max(id) as id, estimated_pick_up_date, equipment_record_id
        FROM equipment_shipping_record_line
        GROUP BY equipment_record_id
    ) shipping_line ON shipping_line.equipment_record_id = er.id
WHERE l.name is not NULL AND l.capability_factory = 1
GROUP BY locationName";

        $stmt = $this->connection->prepare($sql);
        $odpData = $stmt->executeQuery()->fetchAllAssociative();

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('l.name as y')
            ->addSelect('COUNT(DISTINCT(er)) AS value')
            ->addSelect("'YT with open CRAB' AS x")
            ->addSelect('l.id as locationId')
            ->from(Crab::class, 'c')
            ->leftJoin(EquipmentRecord::class, 'er', Join::WITH, 'er.id = c.equipmentRecord')
            ->leftJoin(Location::class, 'l', Join::WITH, 'er.manufacturerLocation = l.id')
            ->where('(er.greenTagDate < er.yellowTagDate OR er.greenTagDate IS NULL)')
            ->andWhere('er.yellowTagDate is not NULL')
            ->andWhere('c.status != :closed')
            ->groupBy('y')
            ->setParameter('closed', Crab::CLOSED)
        ;

        $crabData = $queryBuilder->getQuery()->getResult();
        $rows = [];
        foreach ($odpData as $odp) {
            $location = $odp['locationName'];
            $locationId = $odp['locationId'];
            foreach ($odp as $key => $value) {
                if (\in_array($key, ['locationName', 'locationId'], true)) {
                    continue;
                }
                $rows[] = ['value' => $value, 'x' => $key, 'y' => $location, 'locationId' => $locationId];
            }
        }

        $classIri = $this->iriConverter->getIriFromResource(Location::class, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        $provider = new ReportDataProvider(array_merge($rows, $crabData));
        $provider->setMetadataExtractor(static function (array $results) use ($classIri): array {
            $yIris = [];
            foreach ($results as $result) {
                $yIris[$result['y']] = \sprintf('%s/%s', $classIri, $result['locationId']);
            }

            $xIris = [
                'Backlog' => EquipmentRecordOdpFilter::NOT_SHIPPED,
                'Number Green Tagged in previous 2 Weeks' => EquipmentRecordOdpFilter::GT_PAST,
                'Number Green Tagging within next 2 weeks' => EquipmentRecordOdpFilter::GT_NEXT,
                'Number Late' => EquipmentRecordOdpFilter::LATE,
                'Number Shipped in previous 2 weeks' => EquipmentRecordOdpFilter::SHIPPED_PAST,
                'Number Shipping within next 2 weeks' => EquipmentRecordOdpFilter::SHIPPED_NEXT,
                'GT Not Shipped (GTNS)' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED,
                'YT Not Shipped (YTNS)' => EquipmentRecordOdpFilter::YT_NOT_SHIPPED,
                'Available For Sale (AFS)' => EquipmentRecordOdpFilter::AVAILABLE_FOR_SALE,
                'YT with open CRAB' => EquipmentRecordOdpFilter::YT_CRAB,
            ];

            return [
                ReportHandlerInterface::METADATA_IRIS_X_KEY => $xIris,
                ReportHandlerInterface::METADATA_IRIS_Y_KEY => $yIris,
            ];
        });

        return $provider;
    }
}
