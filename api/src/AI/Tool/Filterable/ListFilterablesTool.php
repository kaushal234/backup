<?php

declare(strict_types=1);

namespace App\AI\Tool\Filterable;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\FilterableRegistry;
use App\AI\Tool\ToolInterface;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[FeatureDoc(path: 'ai-filterable.md')]
#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class ListFilterablesTool implements ToolInterface
{
    private const string NAME = 'list_filterables';
    private const string DESCRIPTION = 'List all entity types that can be filtered via the filter_entity tool. '
        .'Call this first when the user asks to list/find/count business entities and you are not sure which entity name to use. '
        .'Then call describe_filterable to learn the available filters for a specific entity.';

    public function __construct(private FilterableRegistry $registry)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'entities' => array_values(array_map(
                static fn (AbstractFilterableDefinition $p): array => [
                    'name' => $p->name(),
                    'description' => $p->description(),
                ],
                $this->registry->all(),
            )),
        ];
    }
}
