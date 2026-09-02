<?php

declare(strict_types=1);

namespace App\AI\Tool\Common;

use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
final readonly class FetchEntityCommentsTool extends AbstractFetchEntityTool
{
    private const string NAME = 'fetch_entity_comments';
    private const string DESCRIPTION = 'Fetch the comments / activity history attached to a business entity. Call this after any extract_* tool to retrieve the user comments and exchanges that are not included in the extraction payload, then call fetch_related_entities with the same entityType and id to retrieve the other business entities related to this one (cross-module references). The entityType matches the extract tool you just used (e.g. "sales_forecast" for extract_sales_forecast_information).';

    /**
     * Overridden (not just inherited) so mcp/sdk's attribute discovery, which resolves the handler
     * class from __invoke's declaring class, registers this concrete class rather than the abstract
     * parent (which isn't instantiable and isn't registered as a service).
     */
    public function __invoke(string $entityType, int $id): string
    {
        return parent::__invoke($entityType, $id);
    }

    protected function loadData(string $entityType, object $entity): array
    {
        return $this->entities->findCommentsFor($entityType, $entity);
    }
}
