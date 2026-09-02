<?php

declare(strict_types=1);

namespace App\Report\Handler\HumanResources;

use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Extractor\HumanResources\EmployeeStaffingMetadataExtractor;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class EmployeeStaffingContractByPositionCategoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    final public const X = 'businessUnit.positionClassifications.positionCategory.name';
    final public const Y = 'contractType.name';

    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;
    private readonly EmployeeStaffingMetadataExtractor $metadataExtractor;

    public function __construct(IriConverterInterface $iriConverter, EntityManagerInterface $entityManager, EmployeeStaffingMetadataExtractor $metadataExtractor)
    {
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
        $this->metadataExtractor = $metadataExtractor;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (People::class !== $resourceClass || self::X !== $x || self::Y !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();
        $rootAlias = $mainQueryBuilder->getRootAliases()[0];

        $queriesBuilder::replaceSelectValuePart($mainQueryBuilder, \sprintf('SUM(%s.coefficient)/100 AS value', $rootAlias));

        $queryNameGenerator = new QueryNameGenerator();
        $businessUnitAlias = QueryBuilderHelper::addJoinOnce($mainQueryBuilder, $queryNameGenerator, $rootAlias, 'businessUnit', Join::LEFT_JOIN);
        $regionAlias = QueryBuilderHelper::addJoinOnce($mainQueryBuilder, $queryNameGenerator, $businessUnitAlias, 'region', Join::LEFT_JOIN);
        $subDivisionAlias = QueryBuilderHelper::addJoinOnce($mainQueryBuilder, $queryNameGenerator, $regionAlias, 'subDivision', Join::LEFT_JOIN);
        $positionClassificationsAlias = QueryBuilderHelper::addJoinOnce($mainQueryBuilder, $queryNameGenerator, $businessUnitAlias, 'positionClassifications', Join::LEFT_JOIN);
        $positionAlias = QueryBuilderHelper::addJoinOnce($mainQueryBuilder, $queryNameGenerator, $positionClassificationsAlias, 'positions', Join::LEFT_JOIN);

        $mainQueryBuilder
            ->andWhere(\sprintf('%s.disabled != :disabled', $rootAlias))
            ->andWhere(\sprintf('%s.position = %s.id', $rootAlias, $positionAlias))
            ->setParameter('disabled', true)
        ;

        $resource = null;
        if ($entity = $options['entity'] ?? false) {
            $resource = $this->iriConverter->getResourceFromIri($options['entity']);
        }

        switch (true) {
            case $resource instanceof BusinessUnit:
                $where = \sprintf('%s.businessUnit', $rootAlias);
                $businessUnits = [$resource];
                break;
            case $resource instanceof Region:
                $where = \sprintf('%s.region', $businessUnitAlias);
                $businessUnits = $resource->getBusinessUnits()->getValues();
                break;
            case $resource instanceof SubDivision:
                $where = \sprintf('%s.subDivision', $regionAlias);
                $businessUnits = [];
                foreach ($resource->getRegions() as $region) {
                    $businessUnits[] = $region->getBusinessUnits()->getValues();
                }
                $businessUnits = array_merge(...$businessUnits);
                break;
            case $resource instanceof Division:
                $where = \sprintf('%s.division', $subDivisionAlias);
                $businessUnits = [];
                foreach ($resource->getSubDivisions() as $subDivision) {
                    foreach ($subDivision->getRegions() as $region) {
                        $businessUnits[] = $region->getBusinessUnits()->getValues();
                    }
                }
                $businessUnits = array_merge(...$businessUnits);
                break;
            default:
                $where = null;
                $businessUnits = $this->entityManager->getRepository(BusinessUnit::class)->findAll();
                break;
        }

        if (null !== $where) {
            $mainQueryBuilder
                ->andWhere(\sprintf('%s = :entity', $where))
                ->setParameter('entity', $resource)
            ;
        }

        $provider = new ReportDataProvider(
            (new QueryBuilderExtractor($mainQueryBuilder))(),
            (new QueryBuilderExtractor($queriesBuilder->getXQueryBuilder()))(),
            (new QueryBuilderExtractor($queriesBuilder->getYQueryBuilder()))()
        );

        $this->metadataExtractor
            ->setBusinessUnits(...$businessUnits)
            ->setResource($resource)
        ;

        $provider->setMetadataExtractor($this->metadataExtractor);

        return $provider;
    }

    public function isGranted(?object $user = null): bool
    {
        // We need to authorize a null user because this report is accessible from a command
        return $user instanceof People || null === $user;
    }
}
