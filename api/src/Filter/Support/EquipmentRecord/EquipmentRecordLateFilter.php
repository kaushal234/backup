<?php

declare(strict_types=1);

namespace App\Filter\Support\EquipmentRecord;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EquipmentRecordLateFilter implements FilterInterface
{
    final public const FILTER_LATE = 'late';

    private readonly RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::FILTER_LATE) || !\in_array($request->query->get(static::FILTER_LATE), [true, 'true', '1'], true)) {
            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%s.greenTagDate IS NULL', $queryBuilder->getRootAliases()[0]))
            ->andWhere(\sprintf('%s.firstGreenTagDate IS NULL', $queryBuilder->getRootAliases()[0]))
            ->andWhere(\sprintf('%s.estimatedGreenTagDate < :today', $queryBuilder->getRootAliases()[0]))
            ->setParameter('today', new \DateTime())
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_LATE => [
                'property' => static::FILTER_LATE,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
