<?php

declare(strict_types=1);

namespace App\AI\Tool\Extract\Support;

use App\AI\Service\Extractor\GenericExtractor;
use App\AI\Tool\ToolInterface;
use App\Entity\EquipmentRecord;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class ExtractEquipmentRecordInformationTool implements ToolInterface
{
    private const string NAME = 'extract_equipment_record_information';
    private const string DESCRIPTION = 'Request information precisely about an Equipment Record (a piece of GSE equipment identified by its serial number). After calling this tool, also call fetch_entity_comments and fetch_related_entities with the matching entityType and the same id to retrieve the activity/comments history and the related entities.';

    public function __construct(
        private GenericExtractor $extractor,
    ) {
    }

    public function __invoke(string $serialNumber): string
    {
        return $this->extractor->extract(EquipmentRecord::class, ['serialNumber' => $serialNumber]);
    }
}
