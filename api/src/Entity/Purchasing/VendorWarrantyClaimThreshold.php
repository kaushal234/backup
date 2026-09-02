<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\Location;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('location')]
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Put(
            security: "is_granted('VENDOR_WARRANTY_CLAIM_THRESHOLD_WRITE_VOTER', object)"
        ),
        new Post(
            securityPostDenormalize: "is_granted('VENDOR_WARRANTY_CLAIM_THRESHOLD_WRITE_VOTER', object)"
        ),
    ],
    routePrefix: 'purchasing',
    normalizationContext: ['groups' => ['vendor_warranty_claim_threshold', 'location_public', 'currency']],
    denormalizationContext: ['groups' => ['vendor_warranty_claim_threshold:write']],
)]
class VendorWarrantyClaimThreshold
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['vendor_warranty_claim_threshold', 'vendor_warranty_claim_threshold:write'])]
    public Location $location;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Groups(['vendor_warranty_claim_threshold', 'vendor_warranty_claim_threshold:write'])]
    public int $threshold;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
