<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Directory\People;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ApiResource(
    operations: [],
)]
#[UniqueEntity(fields: ['guest', 'training'], message: 'The user {{ value }} is already in the attendees list.')]
#[ORM\Table(name: 'trainings_attendees')]
#[ORM\UniqueConstraint(name: 'unique_guest_per_training', columns: ['training_id', 'guest_id'])]
class TrainingAttendee
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'guest_id', nullable: false)]
    private ?People $guest = null;

    #[ORM\Column(name: 'invited', type: 'boolean')]
    private bool $invited = true;

    #[ORM\Column(name: 'answer', type: 'boolean', nullable: true)]
    private ?bool $answer = null;

    #[ORM\ManyToOne(targetEntity: Training::class, inversedBy: 'attendees')]
    #[ORM\JoinColumn(name: 'training_id', nullable: false)]
    private Training $training;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTraining(): Training
    {
        return $this->training;
    }

    public function setTraining(Training $training): self
    {
        $this->training = $training;

        return $this;
    }

    public function getGuest(): ?People
    {
        return $this->guest;
    }

    public function setGuest(People $guest): self
    {
        $this->guest = $guest;

        return $this;
    }

    public function isInvited(): bool
    {
        return $this->invited;
    }

    public function setInvited(bool $invited): self
    {
        $this->invited = $invited;

        return $this;
    }

    public function getAnswer(): ?bool
    {
        return $this->answer;
    }

    public function setAnswer(?bool $answer = null): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
