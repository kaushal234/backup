<?php

declare(strict_types=1);

namespace App\AI\Tool\Filterable;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Filterable\FilterableField;
use App\AI\Filterable\FilterableRegistry;
use App\AI\Tool\ToolInterface;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[FeatureDoc(path: 'ai-filterable.md')]
#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class DescribeFilterableTool implements ToolInterface
{
    private const string NAME = 'describe_filterable';
    private const string DESCRIPTION = 'Return the list of filters supported by a given entity (obtained from list_filterables). '
        .'Each filter has a name, type (string/int/float/bool/date), a multi flag (true = array of values, OR semantic), '
        .'an optional enum of allowed values, and a description. Use this before calling filter_entity to build a correct payload.';

    public function __construct(private FilterableRegistry $registry)
    {
    }

    /**
     * @param string $entity Entity name as returned by list_filterables (e.g. "vendor_warranty_claim")
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $entity): array
    {
        $provider = $this->registry->get($entity);

        if (null === $provider) {
            return [
                'error' => \sprintf('Unknown entity "%s". Call list_filterables to see available entities.', $entity),
            ];
        }

        return [
            'name' => $provider->name(),
            'description' => $provider->description(),
            'fields' => array_map(static fn (FilterableField $f) => $f->toSchema(), $provider->fieldSchema()),
        ];
    }
}
