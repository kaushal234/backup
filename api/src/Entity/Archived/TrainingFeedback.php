<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ApiResource(operations: [])]
#[ORM\Entity]
#[UniqueEntity(fields: ['attendee'])]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_feedback_per_attendee', columns: ['attendee_id'])]
class TrainingFeedback
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $note;

    #[ORM\Column(type: 'integer')]
    private int $contentNote;

    #[ORM\Column(type: 'integer')]
    private int $trainerNote;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToOne(targetEntity: TrainingAttendee::class)]
    #[ORM\JoinColumn(nullable: false)]
    private TrainingAttendee $attendee;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getAttendee(): TrainingAttendee
    {
        return $this->attendee;
    }

    public function setAttendee(TrainingAttendee $attendee): self
    {
        $this->attendee = $attendee;

        return $this;
    }

    public function getContentNote(): int
    {
        return $this->contentNote;
    }

    public function setContentNote(int $contentNote): self
    {
        $this->contentNote = $contentNote;

        return $this;
    }

    public function getTrainerNote(): int
    {
        return $this->trainerNote;
    }

    public function setTrainerNote(int $trainerNote): self
    {
        $this->trainerNote = $trainerNote;

        return $this;
    }
}
