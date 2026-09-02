<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Repository\Directory\PeopleRepository;
use Doctrine\DBAL\Connection;

class VendorWarrantyClaimAverageResolvedTimeByBuyerHandler extends AbstractWarrantyClaimAverageResolved implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly IriConverterInterface $iriConverter;
    private readonly PeopleRepository $peopleRepository;

    public function __construct(Connection $connection, IriConverterInterface $iriConverter, PeopleRepository $peopleRepository)
    {
        parent::__construct($connection);
        $this->iriConverter = $iriConverter;
        $this->peopleRepository = $peopleRepository;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (VendorWarrantyClaim::class !== $resourceClass || 'resolved_time' !== $x || 'buyer' !== $y || null === ($options['location'] ?? null)) {
            return null;
        }

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilderSupplierNumbers = clone $queryBuilder;
        $queryBuilderSupplierNumbers->resetOrderBy();
        $queryBuilderSupplierNumbers->resetGroupBy();
        $queryBuilderSupplierNumbers->select('DISTINCT vwc.supplier_number AS supplierNumbers');

        $location = $this->iriConverter->getResourceFromIri($options['location']);
        if (!$location instanceof Location) {
            return null;
        }

        // Not working since it search BAAN supplier with LN supplier names. To be reworked when a filter by buyer will be available on BusinessPartner entity
        //        /** @var ERPSupplier[] $erpSuppliers */
        //        $erpSuppliers = $this->ERPSupplierRepository->findAllById($queryBuilderSupplierNumbers->executeQuery()->fetchFirstColumn(), $location->getErp());
        $cases = [];
        $erpSuppliers = [];
        //        foreach ($erpSuppliers as $erpSupplier) {
        //            $cases[] = sprintf('WHEN vwc.supplier_number = "%s" THEN "%s"', $erpSupplier->getSuno(), $erpSupplier->getBuyerEmail() ?? 'UNKNOWN');
        //        }

        //        if ([] === $cases) {
        return new ReportDataProvider([], [], []);
        //        }

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
