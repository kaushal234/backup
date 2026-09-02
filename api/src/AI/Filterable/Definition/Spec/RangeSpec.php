<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * Two filters on the same column: $lowerName (>=) and $upperName (<=). Used for date
 * ({@see FilterableField::TYPE_DATE}) and numeric ({@see FilterableField::TYPE_INT}/
 * {@see FilterableField::TYPE_FLOAT}) ranges — the $type drives both the LLM-facing
 * field type and the value coercion.
 */
final readonly class RangeSpec implements FilterSpec
{
    public function __construct(
        private string $path,
        private string $lowerName,
        private string $upperName,
        private string $type,
        private string $desc = '',
        private ?string $lowerDesc = null,
        private ?string $upperDesc = null,
    ) {
    }

    public function toFields(): array
    {
        $prefix = '' === $this->desc ? '' : $this->desc.' ';
        [$defaultLower, $defaultUpper] = $this->defaultDescriptions();

        return [
            new FilterableField($this->lowerName, $this->type, description: $this->lowerDesc ?? $prefix.$defaultLower),
            new FilterableField($this->upperName, $this->type, description: $this->upperDesc ?? $prefix.$defaultUpper),
        ];
    }

    public function applyTo(QueryBuilder $qb, PathResolver $paths, array $filters): void
    {
        $lower = $this->coerce($this->lowerName, $filters[$this->lowerName] ?? null);
        $upper = $this->coerce($this->upperName, $filters[$this->upperName] ?? null);
        if (null === $lower && null === $upper) {
            return;
        }

        $dql = $paths->resolve($qb, $this->path);

        if (null !== $lower) {
            $param = 'p_'.$this->lowerName;
            $qb->andWhere(\sprintf('%s >= :%s', $dql, $param))->setParameter($param, $lower);
        }
        if (null !== $upper) {
            $param = 'p_'.$this->upperName;
            $qb->andWhere(\sprintf('%s <= :%s', $dql, $param))->setParameter($param, $upper);
        }
    }

    private function coerce(string $filterName, mixed $value): \DateTimeImmutable|int|float|null
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return match ($this->type) {
            FilterableField::TYPE_DATE => $this->coerceDate($filterName, $value),
            FilterableField::TYPE_INT => (int) $value,
            FilterableField::TYPE_FLOAT => (float) $value,
            default => throw new \LogicException(\sprintf('Unsupported range type "%s".', $this->type)),
        };
    }

    private function coerceDate(string $filterName, mixed $value): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable((string) $value);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(\sprintf('Filter "%s" expects an ISO-8601 date, got "%s".', $filterName, (string) $value), previous: $e);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function defaultDescriptions(): array
    {
        return FilterableField::TYPE_DATE === $this->type
            ? ['ISO-8601 date — records on/after this date.', 'ISO-8601 date — records on/before this date.']
            : ['Minimum value (inclusive).', 'Maximum value (inclusive).'];
    }
}
