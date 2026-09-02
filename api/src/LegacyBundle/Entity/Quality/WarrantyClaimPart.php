<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Quality;

use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Parts\SparePartsRequest;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'warranty_parts')]
class WarrantyClaimPart
{
    #[ORM\ManyToOne(targetEntity: WarrantyClaim::class, inversedBy: 'parts')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[Ignore]
    public WarrantyClaim $warrantyClaim;

    #[ORM\ManyToOne(targetEntity: SparePartsRequest::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'spr_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['legacy:warranty_claim:parts'])]
    public ?SparePartsRequest $spr = null;

    #[ORM\Column(name: 'supply_it', type: 'string')]
    public string $supplyIt = '';

    #[ORM\Column(name: 'part_number', type: 'string')]
    #[Groups(['legacy:warranty_claim:parts'])]
    public string $partNumber = '';

    #[ORM\Column(name: 'part_description', type: 'string')]
    #[Groups(['legacy:warranty_claim:parts'])]
    public string $partDescription = '';

    #[ORM\Column(name: 'brand', type: 'string')]
    public string $brand = '';

    #[ORM\Column(name: 'quantity', type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['legacy:warranty_claim:parts'])]
    public string $quantity = '0.00';

    #[ORM\Column(name: 'qty_in', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    public ?string $quantityReturned = null;

    #[ORM\Column(name: 'd_in', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $returnedDate = null;

    #[ORM\Column(name: 'um', type: 'string')]
    #[Groups(['legacy:warranty_claim:parts'])]
    public string $unitOfMeasure = '';

    #[ORM\Column(name: 'failure_type', type: 'string')]
    public string $failureType = '';

    #[ORM\Column(name: 'failure_system', type: 'string')]
    public string $failureSystem = '';

    #[ORM\Column(name: 'sn', type: 'string')]
    public string $replacementSerialNumber = '';

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    public ?string $notes = null;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
