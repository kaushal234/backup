<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Validator\Parts\SparePartsRequestGroupsGenerator;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    shortName: 'SbSparePartsRequest',
    operations: [
        new GetCollection(normalizationContext: ['groups' => SparePartsRequest::COLLECTION_NORMALIZATION_GROUPS]),
        new Post(
            denormalizationContext: ['groups' => ['spare_parts_request:create', 'part:admin', 'address_write']],
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_CREATE') or is_granted('MOO_SPR')",
        ),
        new Get(),
        new Put(security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_PARTS') or is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR')"),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => SparePartsRequest::ITEM_NORMALIZATION_GROUPS],
    denormalizationContext: ['groups' => ['spare_parts_request:edit:partial', 'part:admin', 'address_write']],
    validationContext: ['groups' => SparePartsRequestGroupsGenerator::class],
)]
#[ORM\Table(name: 'spare_parts_requests_sb')]
#[ApiFilter(SearchFilter::class, properties: ['sbId'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['part']])]
#[Loggable]
#[Legacy\Synchronize(table: 'spr')]
#[Legacy\ExtraColumn(column: 'parent_id', value: 0)]
#[Legacy\ExtraColumn(column: 'psr_id', value: 0)]
class SBSparePartsRequest extends SparePartsRequest
{
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['spare_parts_request', 'spare_parts_request:create'])]
    public int $sbId;
}
