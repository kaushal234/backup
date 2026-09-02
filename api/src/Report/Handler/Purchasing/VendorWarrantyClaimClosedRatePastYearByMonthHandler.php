<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Purchasing\VendorWarrantyClaim;
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

class VendorWarrantyClaimClosedRatePastYearByMonthHandler implements ReportHandlerInterface
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
        if (VendorWarrantyClaim::class !== $resourceClass || 'vwc_closed_rate' !== $x) {
            return null;
        }

        $openedVendorWarrantyClaimQueryBuilder = $this->entityManager->createQueryBuilder();
        $openedVendorWarrantyClaimQueryBuilder
            ->select('COUNT(vwc) AS value')
            ->addSelect('loc.name AS y')
            ->from(VendorWarrantyClaim::class, 'vwc')
            ->leftJoin(Location::class, 'loc', Join::WITH, 'loc = vwc.location')
            ->groupBy('x, y')
            ->orderBy('x')
        ;

        $closedVendorWarrantyClaimQueryBuilder = clone $openedVendorWarrantyClaimQueryBuilder;

        $openedVendorWarrantyClaimQueryBuilder
            ->addSelect("DATE_FORMAT(vwc.createdAt,'%Y%m') AS x")
            ->setParameter('oneYearAgo', new \DateTime('midnight first day of this month last year'))
            ->where('vwc.createdAt >= :oneYearAgo')
        ;

        $closedVendorWarrantyClaimQueryBuilder
            ->addSelect("DATE_FORMAT(vwc.closedAt,'%Y%m') AS x")
            ->setParameter('oneYearAgo', new \DateTime('midnight first day of this month last year'))
            ->where('vwc.closedAt >= :oneYearAgo')
        ;

        if ('ALL' !== $y) {
            $location = $this->iriConverter->getResourceFromIri($y);
            $openedVendorWarrantyClaimQueryBuilder
                ->andWhere('vwc.location = :location')
                ->setParameter('location', $location)
            ;
            $closedVendorWarrantyClaimQueryBuilder
                ->andWhere('vwc.location = :location')
                ->setParameter('location', $location)
            ;
        }

        $openedVendorWarrantyClaims = (new QueryBuilderExtractor($openedVendorWarrantyClaimQueryBuilder))();
        $closedVendorWarrantyClaims = (new QueryBuilderExtractor($closedVendorWarrantyClaimQueryBuilder))();

        foreach ($openedVendorWarrantyClaims as &$openedVendorWarrantyClaim) {
            foreach ($closedVendorWarrantyClaims as $closedVendorWarrantyClaim) {
                if ($closedVendorWarrantyClaim['x'] === $openedVendorWarrantyClaim['x'] && $closedVendorWarrantyClaim['y'] === $openedVendorWarrantyClaim['y']) {
                    if (0 !== $openedVendorWarrantyClaim['value']) {
                        $openedVendorWarrantyClaim['value'] = round($closedVendorWarrantyClaim['value'] / $openedVendorWarrantyClaim['value'] * 100);
                        continue;
                    }
                    $openedVendorWarrantyClaim['value'] = '0';
                }
            }
        }

        return new ReportDataProvider(
            $openedVendorWarrantyClaims,
            (new LabelExtractor($openedVendorWarrantyClaims, 'x'))(),
            (new LabelExtractor($openedVendorWarrantyClaims, 'y'))()
        );
    }
}
