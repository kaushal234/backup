<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\DiscriminatorFilter;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType(value: 'SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['pdi' => 'App\Entity\Sales\PreDeliveryInspection'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/inspections'
        ),
        new Get(
            uriTemplate: '/inspections/{id}'
        ),
    ],
    normalizationContext: ['groups' => ['inspection']],
    denormalizationContext: ['groups' => ['inspection:write']]
)]
#[ORM\Table(name: 'inspection')]
#[ApiFilter(DiscriminatorFilter::class)]
abstract class AbstractInspection implements UpdatableStatusEntityInterface
{
    public const REQUESTED = 'REQUESTED';
    public const SCHEDULED = 'SCHEDULED';
    public const FAILED = 'FAILED';
    public const SUCCESSFUL = 'SUCCESSFUL';
    public const CLOSED_STATUSES = [self::SUCCESSFUL, self::FAILED];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['inspection'])]
    protected int $id;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['inspection', 'inspection:detail'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    #[Groups(['inspection', 'inspection:detail'])]
    #[Gedmo\Blameable(on: 'create')]
    private ?User $createdBy = null;

    #[ORM\Column(name: 'planned_at', type: 'datetime', nullable: true)]
    #[Groups(['inspection', 'inspection:detail', 'inspection:write'])]
    private ?\DateTimeInterface $plannedAt = null;

    #[ORM\Column(name: 'status', type: 'string')]
    #[Groups(['inspection', 'inspection:detail', 'inspection:status_update', 'odp:view'])]
    #[Assert\Choice(choices: [self::REQUESTED, self::SCHEDULED, self::FAILED, self::SUCCESSFUL])]
    private string $status = self::REQUESTED;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getPlannedAt(): ?\DateTimeInterface
    {
        return $this->plannedAt;
    }

    public function setPlannedAt(?\DateTimeInterface $plannedAt): self
    {
        $this->plannedAt = $plannedAt;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isOpen(): bool
    {
        return !\in_array($this->status, self::CLOSED_STATUSES, true);
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateStatusOnPlannedAt(): void
    {
        if (null !== $this->plannedAt && self::REQUESTED === $this->status) {
            $this->status = self::SCHEDULED;
        }
    }
}
