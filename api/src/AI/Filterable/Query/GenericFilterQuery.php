<?php

declare(strict_types=1);

namespace App\AI\Filterable\Query;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\PathResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Runs the filters declared by an {@see AbstractFilterableDefinition} against Doctrine.
 *
 * Builds a QueryBuilder on the definition's entity class with its default alias, hands it
 * (along with a {@see PathResolver}) to every spec in {@see AbstractFilterableDefinition::fields()}
 * so each can apply its `andWhere`/joins, then applies the default ordering, limit, and
 * `distinct()` when at least one spec joined a to-many relation.
 */
final readonly class GenericFilterQuery
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return object[]
     */
    public function run(AbstractFilterableDefinition $definition, array $filters, int $limit): array
    {
        $alias = $definition->defaultAlias();
        $entityClass = $definition->entityClass();
        $entityManager = $this->registry->getManagerForClass($entityClass);

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \RuntimeException(\sprintf('No ORM entity manager found for class "%s".', $entityClass));
        }

        $qb = $entityManager->getRepository($entityClass)->createQueryBuilder($alias);
        $paths = new PathResolver($entityManager, $entityClass, $alias);

        foreach ($definition->fields() as $spec) {
            $spec->applyTo($qb, $paths, $filters);
        }

        foreach ($definition->defaultOrder() as $column => $direction) {
            $qb->addOrderBy(\sprintf('%s.%s', $alias, $column), $direction);
        }

        if ($paths->needsDistinct()) {
            $qb->distinct();
        }

        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
