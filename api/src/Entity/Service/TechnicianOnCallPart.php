<?php

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\People;
use App\Filter\SimpleSearchFilter;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'technician_on_call_part')]
#[ApiResource(
    operations: [
        new GetCollection(openapi: true),
        new Get(),
        new Post(openapi: true),
        new Put(openapi: true),
        new Delete(openapi: true),
    ],
    routePrefix: 'service',
    denormalizationContext: ['groups' => ['toc:part:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['id'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'partNumber' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'partNumber'])]
#[SoftDeleteable]
class TechnicianOnCallPart
{
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    #[Assert\When(
        expression: 'null === this.vendorPartNumber',
        constraints: [new Assert\NotBlank()],
    )]
    public ?string $partNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    #[Assert\When(
        expression: 'null === this.partNumber',
        constraints: [new Assert\NotBlank()],
    )]
    public ?string $vendorPartNumber = null;

    #[ORM\Column]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    #[Assert\NotBlank]
    public string $description;

    #[ORM\Column(type: 'float')]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    public float $quantity = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Timestampable(on: 'create')]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    public ?\DateTimeInterface $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Blameable(on: 'create')]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    public People $createdBy;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['toc:read:detail', 'toc:part:write', 'toc:update_status'])]
    public bool $defective = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    #[Assert\AtLeastOneOf([
        new Assert\Choice(callback: [TechnicianOnCallReplacement::class, 'names']),
        new Assert\IsNull(),
    ])]
    public ?string $replacement = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['toc:read:detail', 'toc:part:write'])]
    public ?string $comment = null;

    #[ORM\ManyToOne(targetEntity: TechnicianOnCall::class, inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['toc:part:write'])]
    public TechnicianOnCall $technicianOnCall;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['toc:read:detail'])]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
