<?php

declare(strict_types=1);

namespace App\Dto\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\DataProvider\Parts\SparePartsRequestFromTOCDataProvider;
use App\Entity\Parts\SparePartsRequest;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/spare_parts_request_from_toc/{id}',
            outputFormats: ['json'],
            openapi: new Operation(
                summary: 'Returns new SPR values for front form with legacyID of TOC',
                parameters: [
                    new Parameter(
                        name: 'legacyId',
                        in: 'path',
                        required: true,
                        schema: ['type' => 'string', 'minimum' => 1],
                    ),
                ],
            ),
            normalizationContext: ['groups' => SparePartsRequest::ITEM_NORMALIZATION_GROUPS],
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_CREATE') or is_granted('MOO_SPR')",
            provider: SparePartsRequestFromTOCDataProvider::class,
        ),
    ],
    routePrefix: 'parts',
)]
class SparePartsRequestFromTOC
{
    /**
     * @param array<string> $equipmentRecords
     * @param array<string> $customers
     */
    public function __construct(
        #[Groups(groups: ['spare_parts_request'])]
        public string $customer,
        #[Groups(groups: ['spare_parts_request'])]
        public string $sso,
        #[Groups(groups: ['spare_parts_request'])]
        public string $factory,
        #[Groups(groups: ['spare_parts_request'])]
        public string $erpLocation,
        #[Groups(groups: ['spare_parts_request'])]
        public string $sph,
        #[Groups(groups: ['spare_parts_request'])]
        public array $equipmentRecords,
        #[Groups(groups: ['spare_parts_request'])]
        public array $customers,
        #[Groups(groups: ['spare_parts_request'])]
        public ?string $airport = null,
        #[Groups(groups: ['spare_parts_request'])]
        public ?SparePartsRequest $sparePartsRequest = null
    ) {
    }
}
