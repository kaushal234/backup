<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\EntityManagerInterface;

class VendorWarrantyClaimBuyerHistoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly EntityManagerInterface $entityManager;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (VendorWarrantyClaim::class !== $resourceClass || 'buyer_history' !== $x || null === ($options['buyer'] ?? null)) {
            return null;
        }

        $buyer = $this->iriConverter->getResourceFromIri($options['buyer']);
        if (!$buyer instanceof People) {
            return null;
        }

        // To be reworked as said in VendorWarrantyClaimAverageResolvedTimeByBuyerHandler
        //        $suppliersErp = $this->supplierRepository->findAllByEmailBuyer($buyer->getEmail(), $buyer->getBusinessUnit()->getLocation()->getErp());
        //        $supplierNumbers = [];
        //        /** @var ERPSupplier $supplier */
        //        foreach ($suppliersErp as $supplier) {
        //            $supplierNumbers[] = $supplier->getSuno();
        //        }
        //
        //        $queryBuilder = $this->entityManager->createQueryBuilder();
        //        $queryBuilder
        //            ->select('COUNT(vwc) AS value')
        //            ->addSelect("DATE_FORMAT(vwc.createdAt,'%Y') AS y")
        //            ->addSelect('vwc_type.name AS x')
        //            ->from(VendorWarrantyClaim::class, 'vwc')
        //            ->leftJoin(VendorWarrantyClaimType::class, 'vwc_type', Join::WITH, 'vwc_type.id = vwc.type')
        //            ->andWhere('vwc.createdAt > :three_years_ago')
        //            ->andWhere('vwc.supplierNumber IN (:supplierNumbers)')
        //            ->groupBy('x, y')
        //            ->setParameter('three_years_ago', new \DateTime('3 year ago'))
        //            ->setParameter('supplierNumbers', $supplierNumbers, Connection::PARAM_STR_ARRAY);
        //
        //        return new ReportDataProvider(
        //            $results = (new QueryBuilderExtractor($queryBuilder))(),
        //            (new LabelExtractor($results, 'x'))(),
        //            (new LabelExtractor($results, 'y'))()
        //        );

        return new ReportDataProvider([], [], []);
    }
}
