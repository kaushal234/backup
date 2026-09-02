<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use App\Entity\Directory\Location;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\Query\Expr\Join;

class VendorWarrantyClaimsStatusByLocationHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (VendorWarrantyClaim::class !== $resourceClass || 'location.name' !== $x || 'status.name' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $queriesBuilder->getMainQueryBuilder()
            ->addSelect('x_0.id AS location_id')
            ->addSelect('vwc_status.id AS status_id')
            ->leftJoin(VendorWarrantyClaimStatus::class, 'vwc_status', Join::WITH, 'vwc_status = o.status')
            ->where('vwc_status.name NOT IN (:statuses) ')
            ->orderBy('vwc_status.position, x_0.name', 'ASC')
            ->setParameter('statuses', VendorWarrantyClaimStatus::CLOSED_STATUSES);

        $provider = new ReportDataProvider((new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))());

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Location::class, 'location_id')
            ->setY(VendorWarrantyClaimStatus::class, 'status_id')
            ->generate()
        );
    }
}
