<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use ArrayIterator;
use Psl\Type;

/**
 * @phpstan-type ReportStructure array{"@id": non-empty-string, "@type": non-empty-string, x: non-empty-string}
 *
 * @psalm-type ReportStructure = array{"@id": non-empty-string, "@type": non-empty-string, x: non-empty-string}
 */
final class Report implements ResourceInterface
{
    /**
     * @param non-empty-string $iri
     * @param non-empty-string $x
     * @param array<mixed>     $xTotals
     * @param array<mixed>     $yTotals
     * @param array<mixed>     $rows
     * @param array<mixed>     $metadata
     */
    public function __construct(
        public readonly string $iri,
        public readonly string $x,
        public readonly int|float $total,
        public readonly ?string $y = null,
        public readonly array $xTotals = [],
        public readonly array $yTotals = [],
        public array $rows = [],
        public readonly array $metadata = [],
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Report'),
            'x' => Type\non_empty_string(),
            'y' => Type\optional(Type\nullable(Type\string())),
            'total' => Type\union(Type\float(), Type\int()),
        ], allow_unknown_fields: true);
    }

    public function sortRowsByTotal(string $direction): void
    {
        $totals = [];

        foreach ($this->rows as $x => $yRows) {
            $totals[$x] = array_sum(array_column($yRows, 'value'));
        }

        $direction = mb_strtoupper($direction);
        'ASC' === $direction ? asort($totals) : arsort($totals);

        $sorted = [];
        foreach (array_keys($totals) as $x) {
            $sorted[$x] = $this->rows[$x];
        }

        $this->rows = $sorted;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator((array) $this);
    }
}
