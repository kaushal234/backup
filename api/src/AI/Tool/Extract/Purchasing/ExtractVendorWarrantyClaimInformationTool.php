<?php

declare(strict_types=1);

namespace App\AI\Tool\Extract\Purchasing;

use App\AI\Service\Extractor\GenericExtractor;
use App\AI\Tool\ToolInterface;
use App\Entity\Purchasing\VendorWarrantyClaim;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class ExtractVendorWarrantyClaimInformationTool implements ToolInterface
{
    private const string NAME = 'extract_vendor_warranty_claim_information';
    private const string DESCRIPTION = 'Request information precisely about a Vendor Warranty Claim (VWC). A VWC can be either NCR-based (derived from a Non-Conformity Record, includes a nonConformity payload) or WC-based (derived from a legacy Warranty Claim, includes a warrantyClaimId). After calling this tool, also call fetch_entity_comments and fetch_related_entities with the matching entityType and the same id to retrieve the activity/comments history and the related entities.';

    public function __construct(
        private GenericExtractor $extractor,
    ) {
    }

    public function __invoke(int $id): string
    {
        return $this->extractor->extract(VendorWarrantyClaim::class, ['id' => $id]);
    }
}
