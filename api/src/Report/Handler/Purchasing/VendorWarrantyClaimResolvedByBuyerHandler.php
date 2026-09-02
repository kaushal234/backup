<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Repository\Directory\PeopleRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class VendorWarrantyClaimResolvedByBuyerHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly Connection $connection;
    private readonly IriConverterInterface $iriConverter;
    private readonly PeopleRepository $peopleRepository;

    public function __construct(Connection $connection, IriConverterInterface $iriConverter, PeopleRepository $peopleRepository)
    {
        $this->connection = $connection;
        $this->iriConverter = $iriConverter;
        $this->peopleRepository = $peopleRepository;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (VendorWarrantyClaim::class !== $resourceClass || 'buyer_vwc_closed' !== $x || null === ($options['location'] ?? null)) {
            return null;
        }

        $location = $this->iriConverter->getResourceFromIri($options['location']);
        if (!$location instanceof Location) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('ROUND(SUM(CASE vwc_status.name WHEN :closedResolved THEN 1 ELSE 0 END) * 100 / COUNT(*), 2) as value')
            ->addSelect("DATE_FORMAT(vwc.created_at,'%Y-%m') AS x")
            ->from('vendor_warranty_claims', 'vwc')
            ->leftJoin('vwc', 'user', 'user', 'user.id = vwc.assignee_id')
            ->leftJoin('vwc', 'vendor_warranty_claim_status', 'vwc_status', 'vwc_status.id = vwc.status_id')
            ->where("vwc_status.name LIKE 'CLOSED%'")
            ->andWhere('vwc.created_at > :one_years_ago')
            ->andWhere('vwc.location_id = :location')
            ->groupBy('x, y')
            ->orderBy('x, y')
            ->setParameter('one_years_ago', (new \DateTime('midnight first day of this month last year'))->format('Y-m-d'), ParameterType::STRING)
            ->setParameter('closedResolved', VendorWarrantyClaimStatus::CLOSED_RESOLVED, ParameterType::STRING)
            ->setParameter('location', $location->getId(), ParameterType::INTEGER)
        ;

        $queryBuilderSupplierNumbers = clone $queryBuilder;
        $queryBuilderSupplierNumbers->resetGroupBy();
        $queryBuilderSupplierNumbers->resetOrderBy();
        $queryBuilderSupplierNumbers->select('DISTINCT vwc.supplier_number AS supplierNumbers');

        return new ReportDataProvider([], [], []);
        // To be reworked as said in VendorWarrantyClaimAverageResolvedTimeByBuyerHandler

        //        $erpSuppliers = $this->ERPSupplierRepository->findAllById($queryBuilderSupplierNumbers->executeQuery()->fetchFirstColumn(), $location->getErp());
        //        $cases = [];
        //        foreach ($erpSuppliers as $erpSupplier) {
        //            $cases[] = sprintf('WHEN vwc.supplier_number = "%s" THEN "%s"', $erpSupplier->getSuno(), $erpSupplier->getBuyerEmail() ?? 'UNKNOWN');
        //        }
        //
        //        if ([] === $cases) {
        //            return new ReportDataProvider([], [], []);
        //        }
        //
        //        $queryBuilder->addSelect(sprintf('CASE %s ELSE "UNKNOWN" END AS y', implode("\n ", $cases)));
        //        $results = $queryBuilder->executeQuery()->fetchAllAssociative();
        //
        //        return new ReportDataProvider(
        //            $results,
        //            [],
        //            []
        //        );
    }
}
