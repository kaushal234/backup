<?php

declare(strict_types=1);

namespace App\Report\Handler\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\DBAL\Connection;

class OnTimeDeliveryPlanningStabilityHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly Connection $connection,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (EquipmentRecord::class !== $resourceClass || 'stability' !== $x) {
            return null;
        }

        $factory = $this->iriConverter->getResourceFromIri($y);
        if (!$factory instanceof Location) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('er.id AS erId')
            ->addSelect('er.serial_number AS serialNumber')
            ->addSelect('log.created_at AS logCreatedAt')
            ->addSelect('log.change_set AS changeSet')
            ->addSelect('factory_order.factory_promised_delivery_date AS factoryPromisedDeliveryDate')
            ->from('equipment_records', 'er')
            ->innerJoin('er', 'activity', 'log', "log.resource LIKE '/equipment_records/%' AND er.id = SUBSTRING(log.resource, 20)")
            ->innerJoin('er', 'sales_order_factory', 'factory_order', 'er.id = factory_order.equipment_record_id')
            ->where($queryBuilder->expr()->gt('log.created_at', ':six_month_ago'))
            ->andWhere($queryBuilder->expr()->eq('log.action', ':update'))
            ->andWhere($queryBuilder->expr()->like('log.change_set', ':estimated'))
            ->andWhere($queryBuilder->expr()->eq('er.manufacturer_location_id', ':factory'))
            ->andWhere($queryBuilder->expr()->isNotNull('log.action'))
            ->andWhere($queryBuilder->expr()->isNotNull('log.change_set'))
            ->setParameters([
                'update' => 'update',
                'estimated' => '%estimatedGreenTagDate%',
                'factory' => (string) $factory->getId(),
                'six_month_ago' => (new \DateTime('midnight first day of this month -6 months'))->format('Y-m-d'),
            ]);

        $logs = $queryBuilder->fetchAllAssociative();
        $rows = [];

        foreach ($logs as $log) {
            $changeSet = json_decode($log['changeSet'], true);
            if (!\is_array($changeSet)) {
                continue;
            }

            if (!isset($changeSet['estimatedGreenTagDate']) || \count($changeSet['estimatedGreenTagDate']) < 2) {
                continue;
            }

            $previousEstimatedGtDateStr = $changeSet['estimatedGreenTagDate'][0] ?? null;
            $newEstimatedGtDateStr = $changeSet['estimatedGreenTagDate'][1] ?? null;

            if (!\is_string($previousEstimatedGtDateStr) || !\is_string($newEstimatedGtDateStr) || empty($previousEstimatedGtDateStr) || empty($newEstimatedGtDateStr)) {
                continue;
            }

            try {
                $previousEstimatedGtDate = new \DateTime($previousEstimatedGtDateStr);
                $newEstimatedGtDate = new \DateTime($newEstimatedGtDateStr);
            } catch (\Exception) {
                continue;
            }

            $diffDays = abs($previousEstimatedGtDate->diff($newEstimatedGtDate)->days);

            // Skip if change is 7 days or less
            if ($diffDays <= 7) {
                continue;
            }

            $serialNumber = $log['serialNumber'];
            $logDate = new \DateTime($log['logCreatedAt']);
            $month = $logDate->format('Y-m');
            $logMessage = \sprintf('From %s To %s', $previousEstimatedGtDate->format('Y-m-d'), $newEstimatedGtDate->format('Y-m-d'));

            // Report: More than 7 days difference in estimated GT date changes
            $estimatedGtDateChangeLabel = 'More than 7 days difference in estimated GT date changes';
            $estimatedGtDateChangeKey = $month.'|'.$estimatedGtDateChangeLabel;

            if (!isset($rows[$estimatedGtDateChangeKey])) {
                $rows[$estimatedGtDateChangeKey] = [
                    '@type' => 'ReportCell',
                    'x' => $month,
                    'y' => $estimatedGtDateChangeLabel,
                    'value' => 0,
                    'extraData' => [],
                ];
            }

            ++$rows[$estimatedGtDateChangeKey]['value'];
            $rows[$estimatedGtDateChangeKey]['extraData'][$serialNumber][] = $logMessage;

            // Report: More than 15 days between estimated GT and factory promised date
            $factoryPromisedDateStr = $log['factoryPromisedDeliveryDate'] ?? null;

            if (\is_string($factoryPromisedDateStr) && !empty($factoryPromisedDateStr)) {
                $factoryPromisedDate = \DateTime::createFromFormat('Y-m-d', mb_substr($factoryPromisedDateStr, 0, 10));
                if ($factoryPromisedDate instanceof \DateTimeInterface) {
                    $diffWithFactory = abs($factoryPromisedDate->diff($newEstimatedGtDate)->days);

                    if ($newEstimatedGtDate > $factoryPromisedDate && $diffWithFactory > 15) {
                        $estimatedGtDateVsFactoryPromisedDateLabel = 'Estimated GT date is more than 15 days after factory promised date';
                        $estimatedGtDateVsFactoryPromisedDateKey = $month.'|'.$estimatedGtDateVsFactoryPromisedDateLabel;

                        if (!isset($rows[$estimatedGtDateVsFactoryPromisedDateKey])) {
                            $rows[$estimatedGtDateVsFactoryPromisedDateKey] = [
                                '@type' => 'ReportCell',
                                'x' => $month,
                                'y' => $estimatedGtDateVsFactoryPromisedDateLabel,
                                'value' => 0,
                                'extraData' => [],
                            ];
                        }

                        ++$rows[$estimatedGtDateVsFactoryPromisedDateKey]['value'];
                        $rows[$estimatedGtDateVsFactoryPromisedDateKey]['extraData'][$serialNumber][] = \sprintf(
                            'Factory Promised: %s vs Estimated GT: %s',
                            $factoryPromisedDate->format('Y-m-d'),
                            $newEstimatedGtDate->format('Y-m-d'),
                        );
                    }
                }
            }
        }

        ksort($rows);

        return new ReportDataProvider(
            array_values($rows),
            [],
            []
        );
    }
}
