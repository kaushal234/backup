<?php

declare(strict_types=1);

namespace App\AI\Tool\Common;

use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
final readonly class FetchRelatedEntitiesTool extends AbstractFetchEntityTool
{
    private const string NAME = 'fetch_related_entities';
    private const string DESCRIPTION = 'Fetch the other business entities related to a given entity (cross-module references stored in the legacy mod_links table). Call this after fetch_entity_comments (which itself follows any extract_* tool) to discover sibling entities — e.g. the CRABs, TOCs, DMS documents attached to a Sales Forecast — that are not part of the extraction payload. The entityType matches the extract tool you just used (e.g. "sales_forecast" for extract_sales_forecast_information).';

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
        return $this->entities->findRelatedEntitiesFor($entityType, $entity);
    }
}
