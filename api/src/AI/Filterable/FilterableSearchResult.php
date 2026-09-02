<?php

declare(strict_types=1);

namespace App\AI\Filterable;

/**
 * Outcome of {@see Definition\AbstractFilterableDefinition::search()}.
 *
 * `rawCount` is the number of rows returned by Doctrine *before* the per-entity access
 * checker runs, so it reflects whether the query window (limit) was actually saturated.
 * `results` is the post-access-check, summarized list handed back to the LLM. The two
 * counts diverge when the checker denies some rows; the tool layer uses `rawCount` to
 * compute the `truncated` flag accurately.
 */
final readonly class FilterableSearchResult
{
    /**
     * @param array<int, array<string, mixed>> $results
     */
    public function __construct(
        public int $rawCount,
        public array $results,
    ) {
    }
}
