<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\NCR;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Purchasing\Supplier\Supplier;
use App\Entity\Quality\NonConformity;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;

class NonConformityTopSupplierHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    private readonly EntityManagerInterface $entityManager;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (NonConformity::class !== $resourceClass || 'top_ten' !== $x || 'supplier' !== $y || null === ($options['location'] ?? null)) {
            return null;
        }

        $location = $this->iriConverter->getResourceFromIri($options['location']);
        if (!$location instanceof Location) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(ncr) AS value')
            ->addSelect("CONCAT(TRIM(ncr.supplierName), ' - ', ncr.supplierNumber, ' - ', COALESCE(supplier.id, '')) as y")
            ->addSelect('ncr.iFactor AS x')
            ->from(NonConformity::class, 'ncr')
            ->leftJoin('ncr.responsibles', 'res')
            ->leftJoin(Supplier::class, 'supplier', 'WITH', 'supplier.code = ncr.supplierNumber')
            ->where($queryBuilder->expr()->isNotNull('ncr.supplierNumber'))
            ->andWhere($queryBuilder->expr()->isNotNull('ncr.supplierName'))
            ->andWhere($queryBuilder->expr()->eq('ncr.location', ':location'))
            ->andWhere($queryBuilder->expr()->eq('res.name', ':responsible'))
            ->groupBy('ncr.supplierNumber')
            ->orderBy('value', Criteria::DESC)
            ->setMaxResults(10)
            ->setParameters(new ArrayCollection([
                new Parameter('responsible', NonConformity::SUPPLIER),
                new Parameter('location', $location),
            ]));

        if (null !== ($options['from'] ?? null)) {
            $queryBuilder->andWhere($queryBuilder->expr()->gt('ncr.createdAt', ':createdAfter'));
            $queryBuilder->setParameter('createdAfter', (new \DateTime($options['from']))->format('Y-m-d'));
        }
        if (null !== ($options['to'] ?? null)) {
            $queryBuilder->andWhere($queryBuilder->expr()->lt('ncr.createdAt', ':createdBefore'));
            $queryBuilder->setParameter('createdBefore', (new \DateTime($options['to']))->format('Y-m-d'));
        }

        foreach (($options['processes'] ?? []) as $process) {
            $processes[] = $this->iriConverter->getResourceFromIri($process);
        }

        if (!empty($processes)) {
            $queryBuilder
                ->leftJoin('ncr.processes', 'pro')
                ->andWhere($queryBuilder->expr()->in('pro', ':processes'))
            ;
            $queryBuilder->setParameter('processes', $processes);
        }

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queryBuilder))(),
            [],
            []
        );
    }
}
