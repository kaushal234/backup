<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition;

use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Translates a dotted property path (e.g. "enteredBy.username", "createdBy.businessUnit.region.name",
 * "scoring.importanceFactor") into a DQL fragment usable in a WHERE clause, creating the
 * necessary joins on demand. Join deduplication is delegated to
 * {@see QueryBuilderHelper::addJoinOnce()} so the same path referenced by several specs
 * does not duplicate joins.
 *
 * - **Plain field** (`status`)                       → `{rootAlias}.status`
 * - **Embedded field** (`scoring.importanceFactor`)  → `{rootAlias}.scoring.importanceFactor` (no join — Doctrine handles embeddables natively)
 * - **Relation field** (`enteredBy.username`)        → `leftJoin {rootAlias}.enteredBy enteredBy_aN` then `enteredBy_aN.username`
 * - **Multi-hop relation** (`a.b.c.d`)               → cascaded left-joins
 * - **Bare relation** (`enteredBy`)                  → returns `IDENTITY({rootAlias}.enteredBy)` without joining
 *
 * Touching any to-many association on the way flips the {@see needsDistinct()} flag.
 */
final class PathResolver
{
    private readonly QueryNameGenerator $nameGenerator;
    private bool $needsDistinct = false;

    /**
     * @param class-string $rootClass
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $rootClass,
        private readonly string $rootAlias,
    ) {
        $this->nameGenerator = new QueryNameGenerator();
    }

    /**
     * Resolve a dotted path to a DQL fragment, creating joins as needed.
     */
    public function resolve(QueryBuilder $qb, string $path): string
    {
        $segments = explode('.', $path);
        $rootMeta = $this->entityManager->getClassMetadata($this->rootClass);

        // Simple field on root (or embedded field on root — Doctrine accepts root.embeddable.field directly).
        if (1 === \count($segments) || $this->isEmbeddedPath($rootMeta, $segments)) {
            return \sprintf('%s.%s', $this->rootAlias, $path);
        }

        // Otherwise: walk relation segments, joining as we go; the last segment is the field.
        $field = array_pop($segments);

        return \sprintf('%s.%s', $this->ensureJoins($qb, $segments), $field);
    }

    /**
     * Resolve a bare relation as `IDENTITY({rootAlias}.relation)` — usable in `IN (:ids)`
     * without forcing a join. Returns null if the path is not a single to-one relation on root.
     */
    public function resolveIdentity(string $path): ?string
    {
        if (str_contains($path, '.')) {
            return null;
        }

        $meta = $this->entityManager->getClassMetadata($this->rootClass);
        if (!$meta->hasAssociation($path) || $meta->isCollectionValuedAssociation($path)) {
            return null;
        }

        return \sprintf('IDENTITY(%s.%s)', $this->rootAlias, $path);
    }

    public function needsDistinct(): bool
    {
        return $this->needsDistinct;
    }

    /**
     * Ensure all relation segments in $segments are joined; return the final alias.
     *
     * @param string[] $segments
     */
    private function ensureJoins(QueryBuilder $qb, array $segments): string
    {
        $currentAlias = $this->rootAlias;
        /** @var class-string $currentClass */
        $currentClass = $this->rootClass;

        foreach ($segments as $segment) {
            /** @var ClassMetadata<object> $meta */
            $meta = $this->entityManager->getClassMetadata($currentClass);

            // Embedded along the way: keep walking the metadata but don't join — accumulate the DQL prefix.
            if (isset($meta->embeddedClasses[$segment])) {
                $currentAlias = \sprintf('%s.%s', $currentAlias, $segment);
                /** @var class-string $embeddedClass */
                $embeddedClass = $meta->embeddedClasses[$segment]['class'];
                $currentClass = $embeddedClass;
                continue;
            }

            if ($meta->isCollectionValuedAssociation($segment)) {
                $this->needsDistinct = true;
            }

            $currentAlias = QueryBuilderHelper::addJoinOnce(
                $qb,
                $this->nameGenerator,
                $currentAlias,
                $segment,
                Join::LEFT_JOIN,
            );
            $currentClass = $meta->getAssociationTargetClass($segment);
        }

        return $currentAlias;
    }

    /**
     * A path like `scoring.importanceFactor` where the first segment is an embeddable
     * (and the rest are fields/sub-embeddables) is passed through verbatim to DQL.
     *
     * @param string[] $segments
     */
    private function isEmbeddedPath(ClassMetadata $rootMeta, array $segments): bool
    {
        return isset($rootMeta->embeddedClasses[$segments[0]]);
    }
}
