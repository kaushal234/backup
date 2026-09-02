<?php

declare(strict_types=1);

namespace App\AI\Tool\Extract\MIS;

use App\AI\Service\Extractor\GenericExtractor;
use App\AI\Tool\ToolInterface;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class ExtractTroubleTicketInformationTool implements ToolInterface
{
    private const string NAME = 'extract_trouble_ticket_information';
    private const string DESCRIPTION = 'Request information precisely about a Trouble Ticket (TTS). After calling this tool, also call fetch_entity_comments and fetch_related_entities with the matching entityType and the same id to retrieve the activity/comments history and the related entities.';

    public function __construct(
        private GenericExtractor $extractor,
    ) {
    }

    public function __invoke(int $id): string
    {
        return $this->extractor->extract(TroubleTicket::class, ['id' => $id]);
    }
}
