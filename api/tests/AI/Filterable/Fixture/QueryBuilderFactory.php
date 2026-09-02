<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Fixture;

use App\AI\Filterable\Definition\PathResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Builds a real {@see QueryBuilder} + {@see PathResolver} pair backed by a mocked
 * EntityManager. Suitable for spec unit tests where paths are simple fields on the
 * root entity (no joins, no embeddables) — that is the common case for unit-level
 * coverage of {@see \App\AI\Filterable\Definition\FilterSpec::applyTo()}.
 *
 * For tests that exercise joins/embeddables, configure $associations / $embedded
 * to the desired metadata shape.
 */
trait QueryBuilderFactory
{
    /**
     * @param array<string, bool> $associations assoc name → true if to-many (false = to-one)
     * @param string[]            $embedded     embeddable property names on the root
     * @param class-string        $rootClass
     *
     * @return array{0: QueryBuilder, 1: PathResolver}
     */
    private function buildQueryBuilderAndResolver(
        array $associations = [],
        array $embedded = [],
        string $rootAlias = 'r',
        string $rootClass = \stdClass::class,
    ): array {
        \assert($this instanceof TestCase);

        $metadata = $this->createMock(ClassMetadata::class);
        // @phpstan-ignore-next-line PathResolver only reads array offsets ['class'], not EmbeddedClassMapping methods.
        $metadata->embeddedClasses = array_fill_keys($embedded, ['class' => \stdClass::class]);
        $metadata->method('hasAssociation')->willReturnCallback(
            static fn (string $name): bool => \array_key_exists($name, $associations),
        );
        $metadata->method('isCollectionValuedAssociation')->willReturnCallback(
            static fn (string $name): bool => $associations[$name] ?? false,
        );
        $metadata->method('getAssociationTargetClass')->willReturn(\stdClass::class);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getClassMetadata')->willReturn($metadata);

        $qb = new QueryBuilder($em);
        $qb->select($rootAlias)->from($rootClass, $rootAlias);

        $paths = new PathResolver($em, $rootClass, $rootAlias);

        return [$qb, $paths];
    }
}
