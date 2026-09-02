<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\Spec\BoolSpec;
use App\AI\Filterable\Definition\Spec\ConcatLikeSpec;
use App\AI\Filterable\Definition\Spec\CustomSpec;
use App\AI\Filterable\Definition\Spec\EqSpec;
use App\AI\Filterable\Definition\Spec\ExistsSpec;
use App\AI\Filterable\Definition\Spec\HasManySpec;
use App\AI\Filterable\Definition\Spec\InAnySpec;
use App\AI\Filterable\Definition\Spec\InSpec;
use App\AI\Filterable\Definition\Spec\LikeSpec;
use App\AI\Filterable\Definition\Spec\RangeSpec;
use App\AI\Filterable\FilterableRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Validates that every path declared in any FilterableDefinition resolves against
 * Doctrine metadata. Catches typos and stale paths the moment an entity property
 * is renamed or removed, without requiring the LLM to actually issue a query.
 *
 * Covers:
 *  - spec paths (BoolSpec, EqSpec, ExistsSpec, LikeSpec, InSpec, RangeSpec, HasManySpec,
 *    ConcatLikeSpec, InAnySpec); CustomSpec is opted-out by design (escape hatch).
 *  - defaultOrder() keys.
 *  - HasManySpec collections must be to-many associations on the root entity.
 */
final class FilterableDefinitionPathsTest extends KernelTestCase
{
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        self::bootKernel();
        $registry = self::getContainer()->get('doctrine');
        \assert($registry instanceof ManagerRegistry);
        $this->registry = $registry;
    }

    /**
     * @return iterable<string, array{0: AbstractFilterableDefinition}>
     */
    public static function filterableProvider(): iterable
    {
        self::bootKernel();
        $registry = self::getContainer()->get(FilterableRegistry::class);
        \assert($registry instanceof FilterableRegistry);

        foreach ($registry->all() as $name => $def) {
            yield $name => [$def];
        }
    }

    /**
     * @dataProvider filterableProvider
     */
    public function testAllPathsResolveAgainstDoctrineMetadata(AbstractFilterableDefinition $def): void
    {
        $rootClass = $def->entityClass();
        $errors = [];

        foreach ($def->fields() as $spec) {
            foreach ($this->extractPathsFromSpec($spec) as [$path, $kind]) {
                $error = match ($kind) {
                    'collection' => $this->validateRootCollection($rootClass, $path),
                    default => $this->validatePath($rootClass, $path),
                };
                if (null !== $error) {
                    $errors[] = \sprintf('  - %s: %s', $path, $error);
                }
            }
        }

        foreach (array_keys($def->defaultOrder()) as $orderPath) {
            $error = $this->validatePath($rootClass, $orderPath);
            if (null !== $error) {
                $errors[] = \sprintf('  - defaultOrder[%s]: %s', $orderPath, $error);
            }
        }

        self::assertSame(
            [],
            $errors,
            \sprintf(
                "Invalid path(s) in %s (entity %s):\n%s",
                $def::class,
                $rootClass,
                implode("\n", $errors),
            ),
        );
    }

    /**
     * @param class-string $class
     */
    private function emFor(string $class): EntityManagerInterface
    {
        $em = $this->registry->getManagerForClass($class);
        \assert($em instanceof EntityManagerInterface, \sprintf('No EntityManager handles %s', $class));

        return $em;
    }

    /**
     * @return iterable<array{0: string, 1: 'path'|'collection'}>
     */
    private function extractPathsFromSpec(FilterSpec $spec): iterable
    {
        if ($spec instanceof CustomSpec) {
            return;
        }

        if ($spec instanceof HasManySpec) {
            yield [$this->readPrivate($spec, 'collection'), 'collection'];

            return;
        }

        if ($spec instanceof BoolSpec
            || $spec instanceof EqSpec
            || $spec instanceof ExistsSpec
            || $spec instanceof LikeSpec
            || $spec instanceof InSpec
            || $spec instanceof RangeSpec
        ) {
            yield [$this->readPrivate($spec, 'path'), 'path'];

            return;
        }

        if ($spec instanceof ConcatLikeSpec || $spec instanceof InAnySpec) {
            foreach ($this->readPrivate($spec, 'paths') as $path) {
                yield [$path, 'path'];
            }

            return;
        }

        self::fail(\sprintf('Unhandled FilterSpec type %s — extend the test extractor.', $spec::class));
    }

    /**
     * @param class-string $rootClass
     */
    private function validatePath(string $rootClass, string $path): ?string
    {
        $segments = explode('.', $path);
        $currentClass = $rootClass;
        $embeddedPrefix = '';

        $last = array_key_last($segments);
        foreach ($segments as $i => $segment) {
            try {
                $meta = $this->emFor($currentClass)->getClassMetadata($currentClass);
            } catch (MappingException $e) {
                return \sprintf('cannot load metadata for %s (%s)', $currentClass, $e->getMessage());
            }

            $qualified = '' === $embeddedPrefix ? $segment : $embeddedPrefix.'.'.$segment;

            // Embedded: stay on the same metadata (Doctrine exposes dotted field names for embeddables).
            if (isset($meta->embeddedClasses[$segment])) {
                if ($i === $last) {
                    return \sprintf('"%s" resolves to an embeddable, not a scalar field', $qualified);
                }
                $embeddedPrefix = $qualified;
                continue;
            }

            if ($meta->hasAssociation($qualified)) {
                if ($i === $last) {
                    return null;
                }
                /** @var class-string $next */
                $next = $meta->getAssociationTargetClass($qualified);
                $currentClass = $next;
                $embeddedPrefix = '';
                continue;
            }

            if ($meta->hasField($qualified)) {
                if ($i === $last) {
                    return null;
                }

                return \sprintf('"%s" is a scalar field but path continues past it', $qualified);
            }

            return \sprintf('"%s" is not a field, association or embeddable on %s', $qualified, $currentClass);
        }

        return null;
    }

    /**
     * @param class-string $rootClass
     */
    private function validateRootCollection(string $rootClass, string $collection): ?string
    {
        $meta = $this->emFor($rootClass)->getClassMetadata($rootClass);

        if (!$meta->hasAssociation($collection)) {
            return \sprintf('"%s" is not an association on %s', $collection, $rootClass);
        }

        if (!$meta->isCollectionValuedAssociation($collection)) {
            return \sprintf('"%s" is not a to-many association on %s', $collection, $rootClass);
        }

        return null;
    }

    private function readPrivate(object $obj, string $property): mixed
    {
        $ref = new \ReflectionProperty($obj, $property);

        return $ref->getValue($obj);
    }
}
