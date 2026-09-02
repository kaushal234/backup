<?php

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Directory\People;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'technician_on_call_defective_part')]
#[ApiResource(operations: [])]
#[SoftDeleteable]
class TechnicianOnCallDefectivePart
{
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    #[Assert\NotBlank]
    public string $partNumber;

    #[ORM\Column]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    #[Assert\NotBlank]
    public string $description;

    #[ORM\Column(type: 'float')]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    public float $quantity = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Timestampable(on: 'create')]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    public ?\DateTimeInterface $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Blameable(on: 'create')]
    #[Groups(['toc:read:detail', 'toc:update_status'])]
    public People $createdBy;

    #[ORM\ManyToOne(targetEntity: TechnicianOnCall::class, inversedBy: 'defectiveParts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['toc:update_status'])]
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
