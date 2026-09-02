<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Purchasing\VendorWarrantyClaimController;
use App\Entity\Activity\Comment;
use App\Entity\Quality\NonConformity;
use App\Serializer\Normalizer\ActivityNormalizer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: 'App\Repository\Purchasing\NCRVendorWarrantyClaimRepository')]
#[ApiResource(
    shortName: 'NcrVendorWarrantyClaim',
    operations: [
        new GetCollection(
            paginationItemsPerPage: 2000,
            normalizationContext: ['groups' => VendorWarrantyClaim::COLLECTION_NORMALIZATION_GROUPS],
        ),
        new Post(
            denormalizationContext: ['groups' => ['vendor_warranty_claim:create', 'part:admin']],
            security: "is_granted('FEATURE_NCR_VENDOR_WARRANTY_CLAIM_CREATE')",
        ),
        new Get(),
        new Put(security: "is_granted('VENDOR_WARRANTY_CLAIM_VOTER', object)"),
        new Put(
            uriTemplate: '/ncr_vendor_warranty_claims/{id}/status',
            controller: VendorWarrantyClaimController::class,
            security: "is_granted('VENDOR_WARRANTY_CLAIM_STATUS_VOTER', object)",
            name: 'update_ncr_vendor_warranty_claim_status',
        ),
    ],
    routePrefix: 'purchasing',
    normalizationContext: ['groups' => VendorWarrantyClaim::ITEM_NORMALIZATION_GROUPS, ActivityNormalizer::NORMALIZE_ACTIVITY_ATTRIBUTE => 'both'],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
    extraProperties: [
        Comment::VENDOR_USER_COMMENTABLE => true,
    ],
)]
#[ORM\Table(name: 'vendor_warranty_claim_ncr')]
#[ApiFilter(SearchFilter::class, properties: ['nonConformity.id' => 'exact'])]
class NCRVendorWarrantyClaim extends VendorWarrantyClaim
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\NonConformity', inversedBy: 'vendorWarrantyClaims')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:light', 'vendor_warranty_claim:create'])]
    public NonConformity $nonConformity;
}
