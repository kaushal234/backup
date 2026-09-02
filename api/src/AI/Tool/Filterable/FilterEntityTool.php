<?php

declare(strict_types=1);

namespace App\AI\Tool\Filterable;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Filterable\FilterableRegistry;
use App\AI\Tool\ToolInterface;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[FeatureDoc(path: 'ai-filterable.md')]
#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class FilterEntityTool implements ToolInterface
{
    private const string NAME = 'filter_entity';
    private const string DESCRIPTION = <<<'TXT'
        Filter business entities by arbitrary criteria. The "entity" name must come from list_filterables,
        and the "filters" keys must match the schema returned by describe_filterable. Multi-valued filters
        take arrays (OR semantic). Filters are combined with AND. Returns a compact list of matching entities
        with a "count" and "truncated" flag.

        The "filters" parameter MUST be a JSON object mapping filter names to values, NOT a list of objects.
        Correct example:
          {"entity":"vendor_warranty_claim","filters":{"statuses":["PENDING","VENDOR_TO_RESPOND"],"supplierNumbers":["TIE0002"]}}
        Incorrect (do NOT do this):
          {"filters":[{"name":"statuses","value":["PENDING"]}]}

        ASK BEFORE GUESSING — THIS IS MANDATORY, NOT OPTIONAL. Every key in "filters" MUST match exactly a
        fields[].name returned by describe_filterable for this entity, and every value MUST match the declared
        type/enum. If the user's request contains an ambiguous term, a label you cannot confidently map to a
        field, or a value that is not present in the field's "enum", you MUST NOT call this tool and you MUST NOT
        guess, infer, or pick the "closest" field/value. Instead, reply in plain text asking the user to clarify,
        and offer concrete options drawn from the candidate fields[].name or enum values returned by
        describe_filterable.

        USE EVERY FILTER THE USER MENTIONED — NO PARTIAL CALLS. Each distinct constraint expressed by the user
        MUST appear in "filters". You are forbidden from silently dropping, ignoring, merging, or "deferring" a
        criterion just because it is hard to map. If ANY one of the mentioned criteria is ambiguous or cannot be
        mapped to a field/value, do NOT call this tool with the subset that you did manage to map — instead ask
        the user to clarify that specific criterion, listing the others you already resolved. Only call this tool
        once EVERY mentioned filter is unambiguous AND present in "filters".
        TXT;

    private const int DEFAULT_LIMIT = 50;
    private const int MAX_LIMIT = 200;

    public function __construct(private FilterableRegistry $registry)
    {
    }

    /**
     * @param string               $entity  Entity name (e.g. "vendor_warranty_claim")
     * @param array<string, mixed> $filters Key/value map of filters, keys must match describe_filterable
     * @param int                  $limit   Max results (default 50, max 200)
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $entity, array $filters = [], int $limit = self::DEFAULT_LIMIT): array
    {
        $provider = $this->registry->get($entity);

        if (null === $provider) {
            return [
                'error' => \sprintf('Unknown entity "%s". Call list_filterables to see available entities.', $entity),
            ];
        }

        $normalized = self::normalizeFilters($filters);

        $validationError = $this->registry->validateFilters($provider, $normalized);
        if (null !== $validationError) {
            return ['error' => $validationError];
        }

        $effectiveLimit = max(1, min($limit, self::MAX_LIMIT));

        try {
            $outcome = $provider->search($normalized, $effectiveLimit);
        } catch (\InvalidArgumentException $e) {
            return ['error' => $e->getMessage()];
        }

        return [
            'entity' => $entity,
            'count' => \count($outcome->results),
            'truncated' => $outcome->rawCount >= $effectiveLimit,
            'results' => $outcome->results,
        ];
    }

    /**
     * Accepts the canonical map form {key: value} but also tolerates Mistral's frequent
     * fallback to a list of {name, value} objects (or JSON-encoded strings of those).
     *
     * @param array<mixed> $filters
     *
     * @return array<string, mixed>
     *
     * @internal exposed for unit testing
     */
    public static function normalizeFilters(array $filters): array
    {
        if ([] === $filters) {
            return [];
        }

        $isList = array_is_list($filters);
        if (!$isList) {
            return $filters;
        }

        $normalized = [];
        foreach ($filters as $item) {
            if (\is_string($item)) {
                $decoded = json_decode($item, true);
                if (\is_array($decoded)) {
                    $item = $decoded;
                }
            }

            if (!\is_array($item) || !isset($item['name'])) {
                continue;
            }

            $normalized[(string) $item['name']] = $item['value'] ?? null;
        }

        return $normalized;
    }
}
