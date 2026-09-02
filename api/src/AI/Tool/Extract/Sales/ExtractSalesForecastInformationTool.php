<?php

declare(strict_types=1);

namespace App\AI\Tool\Extract\Sales;

use App\AI\Service\Extractor\GenericExtractor;
use App\AI\Tool\ToolInterface;
use App\Entity\Sales\SalesForecast;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class ExtractSalesForecastInformationTool implements ToolInterface
{
    private const string NAME = 'extract_sales_forecast_information';
    private const string DESCRIPTION = 'Request information precisely about a Sales Forecast (SFR). After calling this tool, also call fetch_entity_comments and fetch_related_entities with the matching entityType and the same id to retrieve the activity/comments history and the related entities.';

    public function __construct(
        private GenericExtractor $extractor,
    ) {
    }

    public function __invoke(int $id): string
    {
        return $this->extractor->extract(SalesForecast::class, ['id' => $id]);
    }
}
