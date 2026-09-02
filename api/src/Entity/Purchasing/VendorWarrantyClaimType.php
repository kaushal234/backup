<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'purchasing',
    normalizationContext: ['groups' => ['vendor_warranty_claim_type']],
    denormalizationContext: [],
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
class VendorWarrantyClaimType implements \Stringable
{
    #[ORM\Column(type: 'string')]
    #[Groups(['vendor_warranty_claim_type'])]
    public string $name;

    #[ORM\Column(type: 'string')]
    #[Groups(['vendor_warranty_claim_type'])]
    public string $description;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
