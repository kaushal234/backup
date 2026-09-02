<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition;

use App\AI\Filterable\Definition\PathResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type AssocConfig array{toMany?: bool, target: class-string}
 * @phpstan-type ClassConfig array{associations?: array<string, AssocConfig>, embedded?: array<string, class-string>}
 */
final class PathResolverTest extends TestCase
{
    public function testSingleSegmentReturnsRootAliasField(): void
    {
        [$qb, $paths] = $this->build([\stdClass::class => []]);

        self::assertSame('r.status', $paths->resolve($qb, 'status'));
        self::assertFalse($paths->needsDistinct());
        self::assertSame([], $qb->getDQLPart('join'));
    }

    public function testEmbeddedPathReturnsDottedFragmentWithoutJoin(): void
    {
        [$qb, $paths] = $this->build([
            \stdClass::class => ['embedded' => ['scoring' => \stdClass::class]],
        ]);

        self::assertSame('r.scoring.importanceFactor', $paths->resolve($qb, 'scoring.importanceFactor'));
        self::assertSame([], $qb->getDQLPart('join'));
    }

    public function testRelationFieldCreatesLeftJoinAndReturnsAliasedField(): void
    {
        [$qb, $paths] = $this->build([
            \stdClass::class => ['associations' => ['enteredBy' => ['target' => \stdClass::class]]],
        ]);

        self::assertSame('enteredBy_a1.username', $paths->resolve($qb, 'enteredBy.username'));

        $joins = $qb->getDQLPart('join');
        self::assertNotEmpty($joins);
        self::assertSame('r.enteredBy', $joins['r'][0]->getJoin());
        self::assertSame('enteredBy_a1', $joins['r'][0]->getAlias());
        self::assertFalse($paths->needsDistinct());
    }

    public function testMultiHopRelationCreatesCascadedJoins(): void
    {
        $bu = 'Test_BusinessUnit_'.uniqid();
        $region = 'Test_Region_'.uniqid();
        eval("class {$bu} {} class {$region} {}");

        [$qb, $paths] = $this->build([
            \stdClass::class => ['associations' => ['createdBy' => ['target' => $bu]]],
            $bu => ['associations' => ['businessUnit' => ['target' => $region]]],
            $region => ['associations' => ['region' => ['target' => \stdClass::class]]],
        ]);

        self::assertSame('region_a3.name', $paths->resolve($qb, 'createdBy.businessUnit.region.name'));

        $joins = $qb->getDQLPart('join')['r'];
        self::assertCount(3, $joins);
    }

    public function testToManyAssociationFlagsNeedsDistinct(): void
    {
        [$qb, $paths] = $this->build([
            \stdClass::class => ['associations' => ['parts' => ['target' => \stdClass::class, 'toMany' => true]]],
        ]);

        $paths->resolve($qb, 'parts.partNumber');

        self::assertTrue($paths->needsDistinct());
    }

    public function testJoinsAreMemoizedAcrossCalls(): void
    {
        [$qb, $paths] = $this->build([
            \stdClass::class => ['associations' => ['enteredBy' => ['target' => \stdClass::class]]],
        ]);

        $paths->resolve($qb, 'enteredBy.username');
        $paths->resolve($qb, 'enteredBy.firstname');

        self::assertCount(1, $qb->getDQLPart('join')['r']);
    }

    public function testRelationAliasDoesNotCollideWithRootAlias(): void
    {
        $other = 'Test_Other_'.uniqid();
        eval("class {$other} {}");

        // root alias is 'r'; relation called "r" must not collide with the root alias.
        [$qb, $paths] = $this->build([
            \stdClass::class => [
                'associations' => [
                    'r' => ['target' => $other],
                ],
            ],
            $other => [],
        ]);

        $paths->resolve($qb, 'r.field');

        $join = $qb->getDQLPart('join')['r'][0];
        self::assertNotSame('r', $join->getAlias());
    }

    public function testResolveIdentityReturnsIdentityShortcutForBareToOneRelation(): void
    {
        [$qb, $paths] = $this->build([
            \stdClass::class => ['associations' => ['enteredBy' => ['target' => \stdClass::class]]],
        ]);

        unset($qb);

        self::assertSame('IDENTITY(r.enteredBy)', $paths->resolveIdentity('enteredBy'));
    }

    public function testResolveIdentityReturnsNullForDottedPath(): void
    {
        [, $paths] = $this->build([\stdClass::class => []]);

        self::assertNull($paths->resolveIdentity('enteredBy.username'));
    }

    public function testResolveIdentityReturnsNullForNonAssociation(): void
    {
        [, $paths] = $this->build([\stdClass::class => []]);

        self::assertNull($paths->resolveIdentity('status'));
    }

    public function testResolveIdentityReturnsNullForToManyAssociation(): void
    {
        [, $paths] = $this->build([
            \stdClass::class => ['associations' => ['parts' => ['target' => \stdClass::class, 'toMany' => true]]],
        ]);

        self::assertNull($paths->resolveIdentity('parts'));
    }

    /**
     * @param array<class-string, ClassConfig> $config
     *
     * @return array{0: QueryBuilder, 1: PathResolver}
     */
    private function build(array $config): array
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $metadataByClass = [];
        foreach ($config as $class => $entry) {
            $meta = $this->createMock(ClassMetadata::class);
            $associations = $entry['associations'] ?? [];
            $embedded = $entry['embedded'] ?? [];

            $meta->embeddedClasses = [];
            foreach ($embedded as $name => $embeddedClass) {
                $meta->embeddedClasses[$name] = ['class' => $embeddedClass];
            }

            $meta->method('hasAssociation')->willReturnCallback(
                static fn (string $name): bool => \array_key_exists($name, $associations),
            );
            $meta->method('isCollectionValuedAssociation')->willReturnCallback(
                static fn (string $name): bool => $associations[$name]['toMany'] ?? false,
            );
            $meta->method('getAssociationTargetClass')->willReturnCallback(
                static fn (string $name): string => $associations[$name]['target'],
            );

            $metadataByClass[$class] = $meta;
        }

        $em->method('getClassMetadata')->willReturnCallback(
            static fn (string $class) => $metadataByClass[$class] ?? throw new \RuntimeException("No metadata configured for {$class}"),
        );

        /** @var class-string $rootClass */
        $rootClass = array_key_first($config);
        $qb = new QueryBuilder($em);
        $qb->select('r')->from($rootClass, 'r');

        $paths = new PathResolver($em, $rootClass, 'r');

        return [$qb, $paths];
    }
}
