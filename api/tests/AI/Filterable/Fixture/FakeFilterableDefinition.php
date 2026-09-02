<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Fixture;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Query\GenericFilterQuery;
use App\AI\Security\EntityAccessCheckerRegistry;

/**
 * Concrete subclass of {@see AbstractFilterableDefinition} used in tests to
 * exercise the base class and its consumers without relying on a real entity.
 */
final readonly class FakeFilterableDefinition extends AbstractFilterableDefinition
{
    /**
     * @param FilterSpec[]                                $fields
     * @param class-string                                $entityClass
     * @param array<string, 'ASC'|'DESC'>                 $order
     * @param \Closure(object): array<string, mixed>|null $summarizer  fn(object): array — defaults to ['id' => spl_object_id]
     */
    public function __construct(
        GenericFilterQuery $query,
        EntityAccessCheckerRegistry $accessCheckers,
        private string $entityName = 'fake',
        private string $entityClass = \stdClass::class,
        private array $fields = [],
        private string $description = 'fake description',
        private array $order = [],
        private ?string $alias = null,
        private ?\Closure $summarizer = null,
    ) {
        parent::__construct($query, $accessCheckers);
    }

    public function name(): string
    {
        return $this->entityName;
    }

    public function entityClass(): string
    {
        return $this->entityClass;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function defaultOrder(): array
    {
        return $this->order;
    }

    public function defaultAlias(): string
    {
        return $this->alias ?? parent::defaultAlias();
    }

    public function summarize(object $entity): array
    {
        if (null !== $this->summarizer) {
            return ($this->summarizer)($entity);
        }

        return ['id' => spl_object_id($entity)];
    }
}
