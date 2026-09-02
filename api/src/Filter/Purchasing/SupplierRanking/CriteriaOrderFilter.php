<?php

declare(strict_types=1);

namespace App\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Common\Filter\OrderFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\SupplierRanking\Criteria;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Enables sorting by dynamic criteria columns: ?order[criteria_{id}]=asc|desc.
 */
final class CriteriaOrderFilter implements FilterInterface
{
    private const string ORDER_PARAMETER = 'order';
    private const string PREFIX = 'criteria_';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (SupplierRanking::class !== $resourceClass) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $order = $request->query->all(self::ORDER_PARAMETER);
        if ([] === $order) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0] ?? null;
        if (null === $rootAlias) {
            return;
        }

        foreach ($order as $property => $direction) {
            if (!\is_string($property) || !str_starts_with($property, self::PREFIX)) {
                continue;
            }

            $criteriaId = (int) mb_substr($property, mb_strlen(self::PREFIX));
            if ($criteriaId <= 0) {
                continue;
            }

            $normalizedDirection = 'asc' === mb_strtolower((string) $direction)
                ? OrderFilterInterface::DIRECTION_ASC
                : OrderFilterInterface::DIRECTION_DESC;

            $notationAlias = $queryNameGenerator->generateJoinAlias('notation_'.$criteriaId);
            $criteriaIdParam = $queryNameGenerator->generateParameterName('criteria_'.$criteriaId);

            $queryBuilder
                ->leftJoin(
                    \sprintf('%s.notations', $rootAlias),
                    $notationAlias,
                    'WITH',
                    \sprintf('IDENTITY(%s.criteria) = :%s', $notationAlias, $criteriaIdParam)
                )
                ->setParameter($criteriaIdParam, $criteriaId)
                ->addOrderBy(\sprintf('%s.notation', $notationAlias), $normalizedDirection)
                ->addOrderBy(\sprintf('%s.id', $rootAlias), OrderFilterInterface::DIRECTION_ASC);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        if (SupplierRanking::class !== $resourceClass) {
            return [];
        }

        $descriptions = [];

        try {
            $entityManager = $this->managerRegistry->getManagerForClass(Criteria::class);

            if ($entityManager instanceof EntityManagerInterface) {
                $rows = $entityManager
                    ->createQueryBuilder()
                    ->select('c.id')
                    ->from(Criteria::class, 'c')
                    ->getQuery()
                    ->getSingleColumnResult();
            } else {
                $rows = [];
            }
        } catch (\Throwable) {
            $rows = [];
        }

        foreach ($rows as $id) {
            $criteriaId = (int) $id;
            if ($criteriaId <= 0) {
                continue;
            }

            $key = \sprintf('%s[%s%d]', self::ORDER_PARAMETER, self::PREFIX, $criteriaId);
            $descriptions[$key] = [
                'property' => self::PREFIX.$criteriaId,
                'type' => 'string',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'default' => mb_strtolower(OrderFilterInterface::DIRECTION_ASC),
                    'enum' => [
                        mb_strtolower(OrderFilterInterface::DIRECTION_ASC),
                        mb_strtolower(OrderFilterInterface::DIRECTION_DESC),
                    ],
                ],
            ];
        }

        if ([] === $descriptions) {
            $descriptions[\sprintf('%s[%s{id}]', self::ORDER_PARAMETER, self::PREFIX)] = [
                'property' => 'criteria_{id}',
                'type' => 'string',
                'required' => false,
                'description' => 'Order by a criteria notation: order[criteria_1]=asc|desc',
            ];
        }

        return $descriptions;
    }
}
