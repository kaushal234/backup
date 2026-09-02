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
use Doctrine\DBAL\ParameterType;

class VendorWarrantyClaimAverageResolvedTimeByLocationHandler extends AbstractWarrantyClaimAverageResolved implements ReportHandlerInterface
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
        if (VendorWarrantyClaim::class !== $resourceClass || 'resolved_time' !== $x || 'location' !== $y || null === ($options['location'] ?? null)) {
            return null;
        }

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->addSelect('location.name AS y')
            ->leftJoin('vwc', 'directory_location', 'location', 'location.id = vwc.location_id')
        ;

        if ('ALL' !== $options['location']) {
            $location = $this->iriConverter->getResourceFromIri($options['location']);
            if (!$location instanceof Location) {
                return null;
            }
            $queryBuilder
                ->andWhere('vwc.location_id = :location')
                ->setParameter('location', $location->getId(), ParameterType::INTEGER)
            ;
        }

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();

        return new ReportDataProvider(
            $results,
            [],
            []
        );
    }
}
