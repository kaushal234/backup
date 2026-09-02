<?php

declare(strict_types=1);

namespace App\Filter\Quality;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FirstArticleQualificationNoPlanFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_NO_PLAN = 'noPlan';

    private readonly RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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

        if (!$request->query->has(self::FILTER_NO_PLAN) || !\in_array($request->query->get(static::FILTER_NO_PLAN), [true, 'true', '1'], true)) {
            return;
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin(PlanItem::class, 'pi', Join::WITH, \sprintf('%s.id = pi.firstArticleQualification', $rootAlias))
            ->groupBy(\sprintf('%s.id', $rootAlias))
            ->having('COUNT(pi) = 0')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_NO_PLAN => [
                'property' => static::FILTER_NO_PLAN,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
