<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Purchasing\Supplier\Supplier;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

class VendorWarrantyClaimFlopSupplierHandler implements ReportHandlerInterface
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
        if (VendorWarrantyClaim::class !== $resourceClass || 'top_ten_flop' !== $x || 'supplier' !== $y) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(vwc.id) AS value')
            ->addSelect("COALESCE(CONCAT(COALESCE(vwc.supplierName, s.name, ''), ' - ', COALESCE(vwc.supplierNumber, ''), ' - ', COALESCE(s.id, '')), '') as y")
            ->addSelect('CAST(COALESCE(vwc.supplierNumber, :defaultSupplier) AS CHAR) AS x')
            ->from(VendorWarrantyClaim::class, 'vwc')
            ->leftJoin(Supplier::class, 's', 'WITH', 's.code = vwc.supplierNumber')
            ->groupBy('vwc.supplierNumber, s.id, s.name, vwc.supplierName')
            ->orderBy('value', 'DESC')
            ->setMaxResults(10)
            ->setParameter('defaultSupplier', 0);

        if (isset($options['location'])) {
            $location = $this->iriConverter->getResourceFromIri($options['location']);
            $queryBuilder->andWhere('vwc.location = :location')
                ->setParameter('location', $location);
        }

        if (isset($options['createdAfter'])) {
            $afterDate = new \DateTime($options['createdAfter']);
            $queryBuilder->andWhere('vwc.createdAt >= :createdAfter')
                ->setParameter('createdAfter', $afterDate);
        }

        if (isset($options['createdBefore'])) {
            $beforeDate = new \DateTime($options['createdBefore']);
            $queryBuilder->andWhere('vwc.createdAt <= :createdBefore')
                ->setParameter('createdBefore', $beforeDate);
        }

        if (isset($options['status'])) {
            $statusArray = [];
            $statuses = \is_array($options['status'])
                ? $options['status']
                : [$options['status']];

            foreach ($statuses as $statusIri) {
                $status = $this->iriConverter->getResourceFromIri($statusIri);
                $statusArray[] = $status;
            }

            if (!empty($statusArray)) {
                $queryBuilder->andWhere('vwc.status IN (:statuses)')
                    ->setParameter('statuses', $statusArray);
            }
        }

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queryBuilder))(),
            [],
            []
        );
    }
}
