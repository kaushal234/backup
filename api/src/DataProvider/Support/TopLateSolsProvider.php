<?php

declare(strict_types=1);

namespace App\DataProvider\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Support\TopLateSolDto;
use App\Entity\Directory\Location;
use App\Entity\Sales\Customer;
use App\Entity\Sales\OrderLine;
use App\Entity\Sales\OrderToFactory;
use Doctrine\ORM\EntityManagerInterface;

class TopLateSolsProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    /**
     * @return TopLateSolDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $locationIri = $context['filters']['manufacturerLocation'] ?? null;
        if (null === $locationIri) {
            return [];
        }

        $location = $this->iriConverter->getResourceFromIri($locationIri);
        if (!$location instanceof Location) {
            return [];
        }

        // Correlated subquery: find the maximum gap (in days) across all EquipmentRecords
        // for a given OrderLine. MAX(DATE_DIFF) avoids ORDER BY + LIMIT 1 inside a
        // subquery, which DQL does not support.
        $subQb = $this->entityManager->createQueryBuilder()
            ->select('MAX(DATE_DIFF(er2.estimatedGreenTagDate, fo2.factoryPromisedDeliveryDate))')
            ->from(OrderToFactory::class, 'fo2')
            ->join('fo2.equipmentRecord', 'er2')
            ->where('fo2.orderLine = ol')
            ->andWhere('er2.greenTagDate IS NULL')
            ->andWhere('er2.firstGreenTagDate IS NULL')
            ->andWhere('er2.estimatedGreenTagDate IS NOT NULL')
            ->andWhere('fo2.factoryPromisedDeliveryDate IS NOT NULL')
            ->andWhere('er2.estimatedGreenTagDate > fo2.factoryPromisedDeliveryDate')
            ->andWhere('er2.manufacturerLocation = :location');

        $qb = $this->entityManager->createQueryBuilder();
        $rows = $qb
            ->select([
                'ol.legacyId                                                              AS sol',
                'b.name                                                                   AS buyer',
                'eu.name                                                                  AS endUser',
                'fo.factoryPromisedDeliveryDate                                           AS promiseDate',
                'er.estimatedGreenTagDate                                                 AS estimatedGreenTagDate',
                'p.name                                                                   AS product',
                'SIZE(ol.factoryOrders)                                                   AS quantity',
                'DATE_DIFF(er.estimatedGreenTagDate, fo.factoryPromisedDeliveryDate)      AS gap',
            ])
            ->from(OrderLine::class, 'ol')
            ->join('ol.factoryOrders', 'fo')
            ->join('fo.equipmentRecord', 'er')
            ->leftJoin('er.buyer', 'b')
            ->leftJoin('er.endUser', 'eu')
            ->leftJoin('er.product', 'p')
            ->where('er.greenTagDate IS NULL')
            ->andWhere('er.firstGreenTagDate IS NULL')
            ->andWhere('er.estimatedGreenTagDate IS NOT NULL')
            ->andWhere('fo.factoryPromisedDeliveryDate IS NOT NULL')
            ->andWhere('er.estimatedGreenTagDate > fo.factoryPromisedDeliveryDate')
            ->andWhere('er.manufacturerLocation = :location')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('b.name'),
                $qb->expr()->notIn('b.name', ':excludedBuyers')
            ))
            ->andWhere('DATE_DIFF(er.estimatedGreenTagDate, fo.factoryPromisedDeliveryDate) = ('.$subQb->getDQL().')')
            ->groupBy('ol.id')
            ->orderBy('MAX(DATE_DIFF(er.estimatedGreenTagDate, fo.factoryPromisedDeliveryDate))', 'DESC')
            ->addOrderBy('MIN(fo.factoryPromisedDeliveryDate)', 'ASC')
            ->setMaxResults(5)
            ->setParameter('location', $location)
            ->setParameter('excludedBuyers', [
                Customer::CUSTOMER_STOCK,
                Customer::CUSTOMER_AVAILABLE_FOR_SALE,
                Customer::CUSTOMER_PROTO,
            ])
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $row): TopLateSolDto => new TopLateSolDto(
                sol: (string) $row['sol'],
                buyer: $row['buyer'],
                endUser: $row['endUser'],
                promiseDate: $row['promiseDate'] instanceof \DateTimeInterface
                    ? $row['promiseDate']->format('Y-m-d')
                    : $row['promiseDate'],
                estimatedGreenTagDate: $row['estimatedGreenTagDate'] instanceof \DateTimeInterface
                    ? $row['estimatedGreenTagDate']->format('Y-m-d')
                    : $row['estimatedGreenTagDate'],
                product: $row['product'],
                quantity: (int) $row['quantity'],
            ),
            $rows
        );
    }
}
