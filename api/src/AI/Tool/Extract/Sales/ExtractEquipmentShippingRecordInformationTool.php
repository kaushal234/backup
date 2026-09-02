<?php

declare(strict_types=1);

namespace App\AI\Tool\Extract\Sales;

use App\AI\Service\Extractor\GenericExtractor;
use App\AI\Tool\ToolInterface;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class ExtractEquipmentShippingRecordInformationTool implements ToolInterface
{
    private const string NAME = 'extract_equipment_shipping_record_information';
    private const string DESCRIPTION = 'Request information precisely about an Equipment Shipping Record (ESR). After calling this tool, also call fetch_entity_comments and fetch_related_entities with the matching entityType and the same id to retrieve the activity/comments history and the related entities.';

    public function __construct(
        private GenericExtractor $extractor,
    ) {
    }

    public function __invoke(int $id): string
    {
        return $this->extractor->extract(EquipmentShippingRecord::class, ['id' => $id]);
    }
}
