<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Filterable\FilterableField;
use App\AI\Filterable\FilterableSearchResult;
use App\AI\Filterable\Query\GenericFilterQuery;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\Entity\Directory\People;
use Doctrine\ORM\EntityNotFoundException;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Directory\PeopleByUsername;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Declarative description of one filterable entity for the AI agent.
 *
 * Each concrete sub-class declares its filter schema via {@see fields()} (a list of
 * {@see FilterSpec} built through the {@see Filter} façade) and projects matching
 * entities via {@see summarize()}. The QueryBuilder construction is delegated to
 * {@see GenericFilterQuery}; the LLM-facing field schema and the security gate are
 * implemented once here in the base class.
 *
 * The `#[AutoconfigureTag('ai.filterable')]` below auto-registers every concrete
 * sub-class as a tagged service, aggregated by {@see \App\AI\Filterable\FilterableRegistry}.
 *
 * Sub-classes don't declare a constructor and stay parameterless; the two engine deps
 * ({@see GenericFilterQuery}, {@see EntityAccessCheckerRegistry}) are autowired through
 * this base constructor.
 */
#[FeatureDoc(path: 'ai-filterable.md')]
#[AutoconfigureTag(name: 'ai.filterable')]
abstract readonly class AbstractFilterableDefinition
{
    public function __construct(
        private GenericFilterQuery $query,
        private EntityAccessCheckerRegistry $accessCheckers,
    ) {
    }

    /**
     * Stable, lowercase, snake_case identifier exposed to the LLM (e.g. "warranty_claim").
     */
    abstract public function name(): string;

    /**
     * Fully-qualified class name of the entity, used for the QueryBuilder and for the
     * per-entity access checker resolved by {@see search()}.
     *
     * @return class-string
     */
    abstract public function entityClass(): string;

    /**
     * Human-readable description shown to the LLM in describe_filterable. Include synonyms
     * the user might say (e.g. "WC", "warranty request").
     */
    abstract public function description(): string;

    /**
     * @return FilterSpec[]
     */
    abstract public function fields(): array;

    /**
     * Produce the compact, LLM-facing summary for a single granted entity. Keep it lean —
     * the LLM gets a list of these, so every extra field × every row inflates token cost.
     *
     * @return array<string, mixed>
     */
    abstract public function summarize(object $entity): array;

    /**
     * Root QueryBuilder alias. Default: first letter of the short class name, lowercased.
     */
    public function defaultAlias(): string
    {
        $short = (new \ReflectionClass($this->entityClass()))->getShortName();

        return mb_strtolower($short[0]);
    }

    /**
     * @return array<string, 'ASC'|'DESC'> column => direction; applied to QueryBuilder::orderBy
     */
    public function defaultOrder(): array
    {
        return [];
    }

    /**
     * Flattens {@see fields()} into the LLM-facing field schema.
     *
     * @return FilterableField[]
     */
    final public function fieldSchema(): array
    {
        return array_merge(
            ...array_map(static fn (FilterSpec $spec): array => $spec->toFields(), $this->fields()),
        );
    }

    /**
     * Runs the declared specs against Doctrine, applies the per-entity access checker to
     * every result (same gate as the extractor — see ai-extractor.md) and returns the
     * compact summaries of granted entities.
     *
     * @param array<string, mixed> $filters
     */
    final public function search(array $filters, int $limit): FilterableSearchResult
    {
        $class = $this->entityClass();
        $raw = $this->query->run($this, $filters, $limit);

        $granted = array_filter(
            $raw,
            fn (object $entity): bool => $this->accessCheckers->isGranted($class, $entity),
        );

        return new FilterableSearchResult(
            rawCount: \count($raw),
            results: array_values(array_map($this->summarize(...), $granted)),
        );
    }

    /**
     * Narrowing helper for {@see summarize()}: the engine only ever hands the definition
     * entities of its own {@see entityClass()}, but we still verify at runtime so the
     * contract holds even if assertions are disabled in production (`zend.assertions=-1`).
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    final protected function ensureInstance(object $entity, string $class): object
    {
        if (!$entity instanceof $class) {
            throw new \LogicException(\sprintf('Expected instance of %s, got %s.', $class, $entity::class));
        }

        return $entity;
    }

    protected function personName(?People $person): ?string
    {
        return $this->safeAccess(static fn (): ?string => null !== $person
            ? \sprintf('%s %s', $person->getFirstname(), $person->getLastname())
            : null);
    }

    protected function legacyPersonName(PeopleByUsername|PeopleById|null $person): ?string
    {
        return $this->safeAccess(static fn (): ?string => null !== $person
            ? \sprintf('%s %s', $person->firstname, $person->lastname)
            : null);
    }

    /**
     * Reads a possibly-stale lazy relation, returning null instead of crashing.
     *
     * Legacy tables carry no referential integrity, so a FK may still point to a
     * row that has since been deleted. Touching such a Doctrine proxy throws an
     * {@see EntityNotFoundException}; we treat the orphaned reference as absent.
     *
     * @template T
     *
     * @param callable(): T $accessor
     *
     * @return T|null
     */
    protected function safeAccess(callable $accessor): mixed
    {
        try {
            return $accessor();
        } catch (EntityNotFoundException) {
            return null;
        }
    }
}
