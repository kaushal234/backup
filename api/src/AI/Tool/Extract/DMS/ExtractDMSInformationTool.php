<?php

declare(strict_types=1);

namespace App\AI\Tool\Extract\DMS;

use App\AI\Service\Extractor\GenericExtractor;
use App\AI\Tool\ToolInterface;
use App\Entity\DMS;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class ExtractDMSInformationTool implements ToolInterface
{
    private const string NAME = 'extract_dms_information';
    private const string DESCRIPTION = 'Request the textual content of a DMS document. DMS stands for Document Management System, where internal processes and procedures are documented. After calling this tool, also call fetch_entity_comments and fetch_related_entities with the matching entityType and the same id to retrieve the activity/comments history and the related entities.';

    public function __construct(
        private GenericExtractor $extractor,
    ) {
    }

    public function __invoke(int $id): string
    {
        return $this->extractor->extract(DMS::class, ['legacyId' => $id]);
    }
}
