<?php

declare(strict_types=1);

namespace App\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Directory\PositionCategory;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PositionCategoryFilter implements FilterInterface
{
    private const PROPERTY = 'positionCategory';

    protected IriConverterInterface $iriConverter;

    protected RequestStack $requestStack;

    public function __construct(IriConverterInterface $iriConverter, RequestStack $requestStack)
    {
        $this->iriConverter = $iriConverter;
        $this->requestStack = $requestStack;
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (People::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the People resource');
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::PROPERTY)) {
            return;
        }

        $value = $request->query->get(self::PROPERTY);

        $positionCategory = $this->iriConverter->getResourceFromIri($value);

        if (!$positionCategory instanceof PositionCategory) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $businessUnitAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'businessUnit', Join::INNER_JOIN);
        $classificationsAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $businessUnitAlias, 'positionClassifications', Join::INNER_JOIN);
        $positionsAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $classificationsAlias, 'positions', Join::INNER_JOIN);

        $positionCategoryParameter = $queryNameGenerator->generateParameterName('positionCategory');
        $queryBuilder
            ->andWhere(\sprintf('%s.position = %s.id', $rootAlias, $positionsAlias))
            ->andWhere(\sprintf('%s.positionCategory = :%s', $classificationsAlias, $positionCategoryParameter))
            ->setParameter($positionCategoryParameter, $positionCategory)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY => [
                'property' => self::PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
