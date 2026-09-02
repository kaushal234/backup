<?php

declare(strict_types=1);

namespace App\AI\Tool\Extract\Support;

use App\AI\Service\Extractor\GenericExtractor;
use App\AI\Tool\ToolInterface;
use LegacyBundle\Entity\Support\ProductDemeritClaim;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class ExtractProductDemeritClaimInformationTool implements ToolInterface
{
    private const string NAME = 'extract_product_demerit_claim_information';
    private const string DESCRIPTION = 'Request information precisely about a Product Demerit Claim (PDC). After calling this tool, also call fetch_entity_comments and fetch_related_entities with the matching entityType and the same id to retrieve the activity/comments history and the related entities.';

    public function __construct(
        private GenericExtractor $extractor,
    ) {
    }

    public function __invoke(int $id): string
    {
        return $this->extractor->extract(ProductDemeritClaim::class, ['id' => $id]);
    }
}
