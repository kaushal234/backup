<?php

declare(strict_types=1);

namespace App\Filter\MIS\Project;

use ApiPlatform\Doctrine\Common\Filter\OrderFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\MIS\Project\Project;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ActivePhaseRevisedClosureAtOrderFilter implements FilterInterface
{
    final public const string PARAMETER = 'activePhaseRevisedClosureAt';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!\array_key_exists(self::PARAMETER, $request->query->all('order'))) {
            return;
        }

        if (Project::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Project resource');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $phasesAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'phases', Join::LEFT_JOIN);

        $queryBuilder
            ->addSelect(\sprintf('
                CASE
                    WHEN %s.status LIKE :pattern AND %s.number = CAST(SUBSTRING(%s.status, -1) AS INTEGER)
                    THEN 0
                    ELSE 1
                END as HIDDEN priority_order', $rootAlias, $phasesAlias, $rootAlias))
            ->addOrderBy('priority_order', 'ASC')
            ->addOrderBy(\sprintf('%s.revisedClosureAt', $phasesAlias), $request->query->all('order')[self::PARAMETER])
            ->setParameter('pattern', 'PHASE_%');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            \sprintf('order[%s]', self::PARAMETER) => [
                'property' => self::PARAMETER,
                'type' => 'string',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'default' => OrderFilterInterface::DIRECTION_ASC,
                    'enum' => [
                        mb_strtolower(OrderFilterInterface::DIRECTION_ASC),
                        mb_strtolower(OrderFilterInterface::DIRECTION_DESC),
                    ],
                ],
            ],
        ];
    }
}
