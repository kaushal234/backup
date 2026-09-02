<?php

declare(strict_types=1);

namespace App\Report\Handler\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Department;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class VendorWarrantyClaimByAssigneeByStatusHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly IriConverterInterface $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (VendorWarrantyClaim::class !== $resourceClass || 'assignee.id' !== $x || 'status.name' !== $y || !isset($options['location'])) {
            return null;
        }

        $location = $this->iriConverter->getResourceFromIri($options['location']);
        if (!$location instanceof Location) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();
        $alias = $mainQueryBuilder->getRootAliases()[0];

        $mainQueryBuilder->leftJoin(\sprintf('%s.status', $alias), 'status');
        $mainQueryBuilder->leftJoin(\sprintf('%s.assignee', $alias), 'assignee');
        $mainQueryBuilder->leftJoin(\sprintf('%s.location', $alias), 'location');

        $mainQueryBuilder->resetDQLPart('select');
        $mainQueryBuilder
            ->addSelect('COUNT(o) AS value')
            ->addSelect('status.name AS y')
            ->addSelect('status.id AS status_id')
            ->where('status.name NOT IN (:statuses)')
            ->andWhere('o.assignee IS NOT NULL')
            ->andWhere('location.id = :location')
            ->orderBy('status.position', 'ASC')
            ->setParameter('statuses', VendorWarrantyClaimStatus::CLOSED_STATUSES)
            ->setParameter('location', $location->getId())
        ;

        if (!isset($options['by_department']) || !(bool) $options['by_department']) {
            $mainQueryBuilder
                ->addSelect("CONCAT(assignee.firstname, ' ', assignee.lastname) AS x")
                ->addSelect('assignee.id AS assignee_id')
            ;

            $class = People::class;
            $field = 'assignee_id';
        } else {
            $mainQueryBuilder->leftJoin(\sprintf('%s.department', 'assignee'), 'department');

            $mainQueryBuilder
                ->addSelect('department.name AS x')
                ->addSelect('department.id AS department_id')
                ->groupBy('status', 'department')
            ;

            $class = Department::class;
            $field = 'department_id';
        }

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))(),
            null,
            null
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX($class, $field)
            ->setY(VendorWarrantyClaimStatus::class, 'status_id')
            ->generate()
        );
    }
}
