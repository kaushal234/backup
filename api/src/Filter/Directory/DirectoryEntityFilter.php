<?php

declare(strict_types=1);

namespace App\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class DirectoryEntityFilter extends AbstractFilter
{
    final public const PROPERTY = 'directoryEntity';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly IriConverterInterface $iriConverter,
        private readonly RequestStack $requestStack,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::PROPERTY => [
                'property' => static::PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        if (!$request->query->has(static::PROPERTY)) {
            return;
        }

        $directoryEntity = $this->iriConverter->getResourceFromIri($request->query->get(static::PROPERTY));
        $rootAlias = $queryBuilder->getRootAliases()[0];
        foreach ($this->properties as $filterableProperty => $v) {
            $propertyAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, $filterableProperty);

            switch ($directoryEntity::class) {
                case $directoryEntity instanceof BusinessUnit:
                    $queryBuilder->andWhere(\sprintf('%s.businessUnit = :directoryEntity', $propertyAlias));
                    break;
                case $directoryEntity instanceof Region:
                    $businessUnitAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $propertyAlias, 'businessUnit');
                    $queryBuilder->andWhere(\sprintf('%s.region = :directoryEntity', $businessUnitAlias));
                    break;
                case $directoryEntity instanceof SubDivision:
                    $businessUnitAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $propertyAlias, 'businessUnit');
                    $regionAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $businessUnitAlias, 'region');
                    $queryBuilder->andWhere(\sprintf('%s.subDivision = :directoryEntity', $regionAlias));
                    break;
                case $directoryEntity instanceof Division:
                    $businessUnitAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $propertyAlias, 'businessUnit');
                    $regionAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $businessUnitAlias, 'region');
                    $subDivisionAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $regionAlias, 'subDivision');
                    $queryBuilder->andWhere(\sprintf('%s.division = :directoryEntity', $subDivisionAlias));
                    break;
                default:
                    throw new BadRequestException('The value passed to the DirectoryEntityFilter should be a businessUnit, a region, a subDivision or a division');
            }
        }

        $queryBuilder->setParameter('directoryEntity', $directoryEntity);
        $queryBuilder->addGroupBy(\sprintf('%s.id', $rootAlias));
    }
}
