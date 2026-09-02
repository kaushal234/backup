<?php

declare(strict_types=1);

namespace App\Report\Handler\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class OnTimeDeliveryPlanningOverviewHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (EquipmentRecord::class !== $resourceClass || 'odp_overview' !== $x) {
            return null;
        }

        $factory = $this->iriConverter->getResourceFromIri($y);
        if (!$factory instanceof Location) {
            return null;
        }

        $queryBuilderEquipmentLate = $this->entityManager->createQueryBuilder();
        $queryBuilderEquipmentLate
            ->select('COUNT(er) as value')
            ->addSelect("'LATE' as y")
            ->addSelect('productFamily.name as x')
            ->from(EquipmentRecord::class, 'er')
            ->join(Product::class, 'product', Join::WITH, 'er.product = product')
            ->join(ProductFamily::class, 'productFamily', Join::WITH, 'product.family = productFamily')
            ->leftJoin('er.buyer', 'buyer')
            ->leftJoin('er.orderFactory', 'orderFactory')
            ->where($queryBuilderEquipmentLate->expr()->isNull('er.firstGreenTagDate'))
            ->andWhere($queryBuilderEquipmentLate->expr()->isNull('er.greenTagDate'))
            ->andWhere(
                $queryBuilderEquipmentLate->expr()->orX(
                    // WO exists (not null and not empty) → use real EGTD
                    $queryBuilderEquipmentLate->expr()->andX(
                        $queryBuilderEquipmentLate->expr()->isNotNull('er.workOrder'),
                        $queryBuilderEquipmentLate->expr()->neq('er.workOrder', ':emptyString'),
                        $queryBuilderEquipmentLate->expr()->isNotNull('er.estimatedGreenTagDate'),
                        $queryBuilderEquipmentLate->expr()->lt('er.estimatedGreenTagDate', ':today')
                    ),
                    // No WO (null or empty) + FPD set → use Factory Promised Date
                    $queryBuilderEquipmentLate->expr()->andX(
                        $queryBuilderEquipmentLate->expr()->orX(
                            $queryBuilderEquipmentLate->expr()->isNull('er.workOrder'),
                            $queryBuilderEquipmentLate->expr()->eq('er.workOrder', ':emptyString')
                        ),
                        $queryBuilderEquipmentLate->expr()->isNotNull('orderFactory.factoryPromisedDeliveryDate'),
                        $queryBuilderEquipmentLate->expr()->lt('orderFactory.factoryPromisedDeliveryDate', ':today')
                    ),
                    // No WO (null or empty) + no FPD → fall back to EGTD
                    $queryBuilderEquipmentLate->expr()->andX(
                        $queryBuilderEquipmentLate->expr()->orX(
                            $queryBuilderEquipmentLate->expr()->isNull('er.workOrder'),
                            $queryBuilderEquipmentLate->expr()->eq('er.workOrder', ':emptyString')
                        ),
                        $queryBuilderEquipmentLate->expr()->isNull('orderFactory.factoryPromisedDeliveryDate'),
                        $queryBuilderEquipmentLate->expr()->isNotNull('er.estimatedGreenTagDate'),
                        $queryBuilderEquipmentLate->expr()->lt('er.estimatedGreenTagDate', ':today')
                    )
                )
            )
            ->andWhere($queryBuilderEquipmentLate->expr()->eq('er.manufacturerLocation', ':location'))
            ->andWhere($queryBuilderEquipmentLate->expr()->orX(
                $queryBuilderEquipmentLate->expr()->isNull('buyer.name'),
                $queryBuilderEquipmentLate->expr()->notIn('buyer.name', ':excludedBuyers')
            ))
            ->groupBy('x')
            ->addGroupBy('y')
            ->setParameter('today', new \DateTime())
            ->setParameter('location', $factory)
            ->setParameter('excludedBuyers', ['**STOCK**', '**AVAILABLE FOR SALE**', '**PROTO**'])
            ->setParameter('emptyString', '')
        ;

        $mondayThisWeek = new \DateTime('monday this week');
        $monday4WeeksAgo = clone $mondayThisWeek;
        $monday4WeeksAgo->modify('-4 weeks');

        $queryBuilderEquipmentGtRecently = $this->connection->createQueryBuilder();
        $queryBuilderEquipmentGtRecently
            ->select('COUNT(*) as value')
            ->addSelect('family.name as x')
            ->addSelect("CONCAT('W-', TIMESTAMPDIFF(WEEK, er.green_tag_date, :mondayThisWeek)) as y")
            ->from('equipment_records', 'er')
            ->join('er', 'products', 'product', 'er.product_id = product.id')
            ->join('product', 'product_families', 'product_family', 'product.family_id = product_family.id')
            ->join('product_family', 'families', 'family', 'product_family.id = family.id')
            ->andWhere($queryBuilderEquipmentGtRecently->expr()->isNotNull('er.green_tag_date'))
            ->andWhere($queryBuilderEquipmentGtRecently->expr()->lt('er.green_tag_date', ':mondayThisWeek'))
            ->andWhere($queryBuilderEquipmentGtRecently->expr()->gt('er.green_tag_date', ':4weeksAgo'))
            ->andWhere($queryBuilderEquipmentGtRecently->expr()->eq('er.manufacturer_location_id', ':location'))
            ->groupBy('x')
            ->addGroupBy('y')
            ->setParameters([
                'mondayThisWeek' => $mondayThisWeek->format('Y-m-d'),
                '4weeksAgo' => $monday4WeeksAgo->format('Y-m-d'),
                'location' => $factory->getId(),
            ])
        ;

        $monday26WeeksLater = clone $mondayThisWeek;
        $monday26WeeksLater->modify('+26 weeks');

        $effectiveDateExpr = "
            CASE
                WHEN er.work_order IS NOT NULL AND er.work_order != ''
                    THEN er.estimated_green_tag_date
                WHEN sof.factory_promised_delivery_date IS NOT NULL
                    THEN sof.factory_promised_delivery_date
                ELSE er.estimated_green_tag_date
            END
        ";

        $queryBuilderEquipmentFutureGt = $this->connection->createQueryBuilder();
        $queryBuilderEquipmentFutureGt
            ->select('COUNT(*) as value')
            ->addSelect('family.name as x')
            ->addSelect("CONCAT('W+', TIMESTAMPDIFF(WEEK, :mondayThisWeek, ($effectiveDateExpr))) as y")
            ->from('equipment_records', 'er')
            ->join('er', 'products', 'product', 'er.product_id = product.id')
            ->join('product', 'product_families', 'product_family', 'product.family_id = product_family.id')
            ->join('product_family', 'families', 'family', 'product_family.id = family.id')
            ->leftJoin('er', 'sales_order_factory', 'sof', 'sof.equipment_record_id = er.id')
            ->andWhere("($effectiveDateExpr) IS NOT NULL")
            ->andWhere("($effectiveDateExpr) < :26weeksLater")
            ->andWhere("($effectiveDateExpr) > :mondayThisWeek")
            ->andWhere($queryBuilderEquipmentFutureGt->expr()->eq('er.manufacturer_location_id', ':location'))
            ->andWhere($queryBuilderEquipmentFutureGt->expr()->isNull('er.green_tag_date'))
            ->groupBy('x')
            ->addGroupBy('y')
            ->setParameters([
                'mondayThisWeek' => $mondayThisWeek->format('Y-m-d'),
                '26weeksLater' => $monday26WeeksLater->format('Y-m-d'),
                'location' => $factory->getId(),
            ])
        ;

        return new ReportDataProvider(
            $results = array_merge(
                $queryBuilderEquipmentLate->getQuery()->getScalarResult(),
                $queryBuilderEquipmentGtRecently->fetchAllAssociative(),
                $queryBuilderEquipmentFutureGt->fetchAllAssociative()
            ),
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
