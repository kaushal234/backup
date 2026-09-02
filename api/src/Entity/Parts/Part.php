<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['spare_parts_request_part' => SparePartsRequestPart::class, 'non_conformity_part' => NonConformityPart::class, 'supplier_corrective_action_request_part' => SupplierCorrectiveActionRequestPart::class, 'vendor_warranty_claim_part' => VendorWarrantyClaimPart::class, 'crab_part' => CrabPart::class])]
#[ApiResource(
    operations: [
        new Get(),
    ],
    routePrefix: 'parts',
    normalizationContext: [],
    denormalizationContext: []
)]
#[ORM\Table(name: 'parts')]
abstract class Part implements \Stringable
{
    #[ORM\Column(type: 'datetime')]
    #[Groups(['part'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[Groups(['part'])]
    #[Gedmo\Blameable(on: 'create')]
    public ?User $createdBy = null;

    #[ORM\Column(type: 'string', length: 40)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 40)]
    #[Groups(['part', 'part:admin'])]
    #[Legacy\Column(column: 'item')]
    public string $partNumber;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['part', 'part:admin'])]
    #[Legacy\Column(column: 'dsca', transformer: Utf8ToHtmlEntities::class)]
    public string $description;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['part', 'part:admin'])]
    #[Legacy\Column(column: 'oqua')]
    public float $quantity;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    #[Assert\Length(max: 3)]
    #[Groups(['part', 'part:admin'])]
    #[Legacy\Column(column: 'um', transformer: Utf8ToHtmlEntities::class)]
    public ?string $unitOfMeasure = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['part'])]
    public ?\DateTimeInterface $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[Groups(['part'])]
    #[Gedmo\Blameable(on: 'update', field: 'deletedAt')]
    public ?User $deletedBy = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['part'])]
    private int $id;

    public function __toString()
    {
        return \sprintf('%s - %s (Qty: %s %s)', $this->partNumber, $this->description, $this->quantity, $this->unitOfMeasure);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
