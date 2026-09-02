<?php

declare(strict_types=1);

namespace App\Report\Handler\Support;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Filter\Support\EquipmentRecord\EquipmentRecordOdpFilter;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class OnTimeDeliveryPlanningSsoHandler implements ReportHandlerInterface
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
        if (EquipmentRecord::class !== $resourceClass || 'dashboard' !== $y || 'salesOrganisation' !== $x) {
            return null;
        }

        $sql = "
SELECT
    l.name AS locationName,
    l.id AS locationId,
    COUNT(CASE WHEN (o.id IS NOT NULL AND er.date_shipped IS NULL) THEN TRUE END) as Backlog,
    COUNT((DATEDIFF(er.green_tag_date, er.yellow_tag_date) > 0 OR DATEDIFF(er.green_tag_date, er.yellow_tag_date) IS NULL) AND er.green_tag_date is not NULL AND er.date_shipped is NULL OR NULL) AS 'GT Not Shipped (GTNS)',
    COUNT((er.yellow_tag_date >= er.green_tag_date OR er.green_tag_date IS NULL) AND er.yellow_tag_date is not NULL AND er.date_shipped is NULL OR NULL) AS 'YT Not Shipped (YTNS)',
    COUNT(
        CASE
            WHEN shipping_line.esrlId IS NOT NULL
                AND shipping_line.estimated_pick_up_date IS NULL
                AND (DATEDIFF(er.green_tag_date, er.yellow_tag_date) > 0 OR DATEDIFF(er.green_tag_date, er.yellow_tag_date) IS NULL)
                AND er.green_tag_date is not NULL AND er.date_shipped is NULL
                AND shipping_line.ship_authorization IS TRUE
                AND shipping_line.code IN ('EXW', 'FCA')
            THEN 1
            END
    ) AS 'GT not shipped with Shipment Authorization Granted, but no Estimated Pick Up Date, Customer responsible for pick up (Incoterms ExW/FCA)',
    COUNT(
        CASE
            WHEN shipping_line.esrlId IS NOT NULL
                AND shipping_line.estimated_pick_up_date IS NULL
                AND (DATEDIFF(er.green_tag_date, er.yellow_tag_date) > 0 OR DATEDIFF(er.green_tag_date, er.yellow_tag_date) IS NULL)
                AND er.green_tag_date is not NULL AND er.date_shipped is NULL
                AND shipping_line.ship_authorization IS TRUE
                AND shipping_line.code NOT IN ('EXW', 'FCA')
            THEN 1
            END
    ) AS 'GT not shipped with Shipment Authorization Granted, but no Estimated Pick Up Date, TLD responsible for pick up (Incoterms NOT ExW/FCA)',
    COUNT(
        CASE
            WHEN shipping_line.esrlId IS NOT NULL
                AND shipping_line.estimated_pick_up_date IS NULL
                AND (DATEDIFF(er.green_tag_date, er.yellow_tag_date) > 0 OR DATEDIFF(er.green_tag_date, er.yellow_tag_date) IS NULL)
                AND er.green_tag_date is not NULL AND er.date_shipped is NULL
                AND shipping_line.ship_authorization IS FALSE
            THEN 1
            END
    ) AS 'GT not shipped with Shipment Authorization NOT granted'
FROM equipment_records er
    LEFT JOIN sales_order_factory f ON f.equipment_record_id = er.id
    LEFT JOIN directory_location l ON er.sales_organisation_id = l.id
    LEFT JOIN sales_order_lines line ON f.order_line_id = line.id
    LEFT JOIN sales_orders o ON o.id = line.order_id
    LEFT JOIN (
        SELECT max(esrl.id) as esrlId, esrl.estimated_pick_up_date, esrl.equipment_record_id, esr.ship_authorization, inc.code
        FROM equipment_shipping_record_line esrl
        LEFT JOIN equipment_shipping_record esr ON esr.id = esrl.equipment_shipping_record_id
        LEFT JOIN incoterm inc ON esr.incoterm_id = inc.id
        GROUP BY equipment_record_id
    ) shipping_line ON shipping_line.equipment_record_id = er.id
WHERE l.name is not NULL AND l.capability_sso = 1
GROUP BY locationName";

        $stmt = $this->connection->prepare($sql);
        $odpData = $stmt->executeQuery()->fetchAllAssociative();

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
        $provider = new ReportDataProvider($rows);
        $provider->setMetadataExtractor(static function (array $results) use ($classIri): array {
            $yIris = [];
            foreach ($results as $result) {
                $yIris[$result['y']] = \sprintf('%s/%s', $classIri, $result['locationId']);
            }

            $xIris = [
                'Backlog' => EquipmentRecordOdpFilter::NOT_SHIPPED,
                'GT Not Shipped (GTNS)' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED,
                'YT Not Shipped (YTNS)' => EquipmentRecordOdpFilter::YT_NOT_SHIPPED,
                'GT not shipped with Shipment Authorization Granted, but no Estimated Pick Up Date, Customer responsible for pick up (Incoterms ExW/FCA)' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_CUSTOMER_RESPONSIBLE,
                'GT not shipped with Shipment Authorization Granted, but no Estimated Pick Up Date, TLD responsible for pick up (Incoterms NOT ExW/FCA)' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_TLD_RESPONSIBLE,
                'GT not shipped with Shipment Authorization NOT granted' => EquipmentRecordOdpFilter::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_NOT_GRANTED,
            ];

            return [
                ReportHandlerInterface::METADATA_IRIS_X_KEY => $xIris,
                ReportHandlerInterface::METADATA_IRIS_Y_KEY => $yIris,
            ];
        });

        return $provider;
    }
}
