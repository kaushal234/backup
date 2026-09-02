<?php

declare(strict_types=1);

namespace App\Filter\Support;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Support\Component;
use App\Entity\Support\EquipmentSerial;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class EquipmentSerialSchematicsFilter implements FilterInterface
{
    final public const FILTER_NAME = 'schematics';

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $context['request'] ?? null;

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(static::FILTER_NAME)) {
            return;
        }

        $value = $request->query->get(static::FILTER_NAME);

        if (EquipmentSerial::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Equipment Serial resource');
        }

        $isSchematics = !\in_array($value, [false, 'false', '0'], true);

        $diagnosticAndExpr = $queryBuilder->expr()->andX(
            $queryBuilder->expr()->like('c.name', ':diag'),
            $queryBuilder->expr()->notLike('c.name', ':diagnostic')
        );

        $schematicExpr = $queryBuilder->expr()->like('c.name', ':schematics');
        $menuTreeExpr = $queryBuilder->expr()->eq('c.name', ':menuTree');

        $orExpr = $queryBuilder->expr()->orX($schematicExpr, $diagnosticAndExpr, $menuTreeExpr);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin(Component::class, 'c', Join::ON, \sprintf('%s.component = c.id', $rootAlias))
            ->setParameter('schematics', '%schem%', 'string')
            ->setParameter('menuTree', 'menu tree', 'string')
            ->setParameter('diag', '%diag%', 'string')
            ->setParameter('diagnostic', '%diagn%', 'string')
        ;

        if ($isSchematics) {
            $queryBuilder->andWhere($orExpr);

            return;
        }

        // schematics=false: return the complement (serials that are not schematics),
        // including serials without a component (which can never be schematics).
        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->not($orExpr),
                $queryBuilder->expr()->isNull(\sprintf('%s.component', $rootAlias)),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_NAME => [
                'property' => static::FILTER_NAME,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
