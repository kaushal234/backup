<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class VendorWarrantyClaimResolvedRatePastYearByMonthHandler implements ReportHandlerInterface
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
        if (VendorWarrantyClaim::class !== $resourceClass || 'vwc_resolved_rate' !== $x) {
            return null;
        }

        $resolvedVendorWarrantyClaimQueryBuilder = $this->entityManager->createQueryBuilder();
        $resolvedVendorWarrantyClaimQueryBuilder
            ->select('COUNT(vwc) AS value')
            ->addSelect("DATE_FORMAT(vwc.closedAt,'%Y%m') AS x")
            ->addSelect('loc.name AS y')
            ->from(VendorWarrantyClaim::class, 'vwc')
            ->leftJoin(VendorWarrantyClaimStatus::class, 'status', Join::WITH, 'status = vwc.status')
            ->leftJoin(Location::class, 'loc', Join::WITH, 'loc = vwc.location')
            ->where('vwc.closedAt >= :oneYearAgo')
            ->groupBy('x, y')
            ->orderBy('x')
            ->setParameter('oneYearAgo', new \DateTime('midnight first day of this month last year'))
        ;

        $closedVendorWarrantyClaimQueryBuilder = clone $resolvedVendorWarrantyClaimQueryBuilder;

        $resolvedVendorWarrantyClaimQueryBuilder
            ->andWhere('status.name = :resolved ')
            ->setParameter('resolved', VendorWarrantyClaimStatus::CLOSED_RESOLVED);

        if ('ALL' !== $y) {
            $location = $this->iriConverter->getResourceFromIri($y);
            $resolvedVendorWarrantyClaimQueryBuilder
                ->andWhere('vwc.location = :location')
                ->setParameter('location', $location)
            ;
            $closedVendorWarrantyClaimQueryBuilder
                ->andWhere('vwc.location = :location')
                ->setParameter('location', $location)
            ;
        }

        $closedVendorWarrantyClaims = (new QueryBuilderExtractor($closedVendorWarrantyClaimQueryBuilder))();
        $resolvedVendorWarrantyClaims = (new QueryBuilderExtractor($resolvedVendorWarrantyClaimQueryBuilder))();

        foreach ($closedVendorWarrantyClaims as &$closedVendorWarrantyClaim) {
            foreach ($resolvedVendorWarrantyClaims as $resolvedVendorWarrantyClaim) {
                if ($closedVendorWarrantyClaim['x'] === $resolvedVendorWarrantyClaim['x'] && $closedVendorWarrantyClaim['y'] === $resolvedVendorWarrantyClaim['y']) {
                    if ('0' !== $closedVendorWarrantyClaim['value']) {
                        $closedVendorWarrantyClaim['value'] = round($resolvedVendorWarrantyClaim['value'] / $closedVendorWarrantyClaim['value'] * 100);
                        continue;
                    }
                    $closedVendorWarrantyClaim['value'] = '0';
                }
            }
        }

        return new ReportDataProvider(
            $closedVendorWarrantyClaims,
            (new LabelExtractor($closedVendorWarrantyClaims, 'x'))(),
            (new LabelExtractor($closedVendorWarrantyClaims, 'y'))()
        );
    }
}
