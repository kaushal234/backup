<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [],
)]
#[ORM\Table(name: 'trainings')]
#[ORM\Index(columns: ['status'], name: 'training_status_idx')]
class Training
{
    /**
     * @var string
     */
    final public const PLANNED = 'PLANNED';
    /**
     * @var string
     */
    final public const CANCELLED = 'CANCELLED';
    /**
     * @var string
     */
    final public const DONE = 'DONE';
    /**
     * @var int
     */
    final public const MAX_ATTENDANCE_LIMIT = 50;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'organizer_id', nullable: false)]
    private People $organizer;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'trainer_id', nullable: false)]
    private People $trainer;

    #[ORM\Column(name: 'meeting_place', type: 'string', length: 255)]
    private string $meetingPlace;

    #[ORM\Column(type: 'string')]
    private string $timezone;

    #[ORM\Column(name: 'starting_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $startingAt;

    #[ORM\Column(name: 'ending_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $endingAt;

    /**
     * @var Collection<TrainingAttendee>
     */
    #[ORM\OneToMany(mappedBy: 'training', targetEntity: TrainingAttendee::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $attendees;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $attendanceLimit = null;

    #[ORM\Column(name: 'status', type: 'string', length: 25)]
    private string $status = self::PLANNED;

    #[ORM\Column(name: 'designation', type: 'string', length: 255)]
    private string $designation;

    #[ORM\Column(name: 'content', type: 'text')]
    private string $content;

    #[ORM\Column(name: 'level', type: 'string', length: 20, nullable: true)]
    private ?string $level = null;

    #[ORM\Column(name: 'open', type: 'boolean')]
    private bool $open;

    #[ORM\Column(name: 'mandatory', type: 'boolean')]
    private bool $mandatory;

    #[ORM\Column(name: 'language', type: 'string', length: 2)]
    private string $language;

    #[ORM\ManyToOne(targetEntity: TrainingCategory::class, inversedBy: 'trainings')]
    private ?TrainingCategory $category = null;

    #[ORM\ManyToOne(targetEntity: TrainingType::class, inversedBy: 'trainings')]
    #[ORM\JoinColumn(name: 'type_id')]
    private ?TrainingType $type = null;

    /**
     * @var Collection<TrainingFile>
     */
    #[ORM\OneToMany(mappedBy: 'training', targetEntity: TrainingFile::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $files;

    public function __construct()
    {
        $this->attendees = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrganizer(): People
    {
        return $this->organizer;
    }

    public function setOrganizer(People $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getTrainer(): People
    {
        return $this->trainer;
    }

    public function setTrainer(People $trainer): self
    {
        $this->trainer = $trainer;

        return $this;
    }

    public function getMeetingPlace(): string
    {
        return $this->meetingPlace;
    }

    public function setMeetingPlace(string $meetingPlace): self
    {
        $this->meetingPlace = $meetingPlace;

        return $this;
    }

    public function getStartingAt(): \DateTimeImmutable
    {
        return $this->startingAt;
    }

    public function setStartingAt(\DateTimeImmutable $startingAt): self
    {
        $this->startingAt = $startingAt->setTimezone(new \DateTimeZone('America/New_York'));

        return $this;
    }

    public function getEndingAt(): \DateTimeImmutable
    {
        return $this->endingAt;
    }

    public function setEndingAt(\DateTimeImmutable $endingAt): self
    {
        $this->endingAt = $endingAt->setTimezone(new \DateTimeZone('America/New_York'));

        return $this;
    }

    /**
     * @return Collection<TrainingAttendee>
     */
    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function addAttendee(TrainingAttendee $attendee): self
    {
        if (!$this->attendees->contains($attendee)) {
            $this->attendees->add($attendee);
            $attendee->setTraining($this);
        }

        return $this;
    }

    public function removeAttendee(TrainingAttendee $attendee): self
    {
        // Stub method

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

    public function getDesignation(): string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function isOpen(): bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    public function setMandatory(bool $mandatory): self
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getType(): ?TrainingType
    {
        return $this->type;
    }

    public function setType(TrainingType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCategory(): TrainingCategory
    {
        return $this->category;
    }

    public function setCategory(TrainingCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<TrainingFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(TrainingFile $file): self
    {
        $this->files[] = $file;
        $file->setTraining($this);

        return $this;
    }

    public function removeFile(TrainingFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getAttendanceLimit(): ?int
    {
        return $this->attendanceLimit;
    }

    public function setAttendanceLimit(?int $attendanceLimit): self
    {
        $this->attendanceLimit = $attendanceLimit;

        return $this;
    }

    public function getCountPresent(): int
    {
        return $this->attendees->filter(static fn (TrainingAttendee $attendee) => true === $attendee->getAnswer())->count();
    }

    public function resetAttendeesAnswers(): self
    {
        $this->attendees->map(static fn (TrainingAttendee $attendee) => $attendee->setAnswer());

        return $this;
    }

    #[Groups('training:detail')]
    public function isMaximumAttendanceReached(bool $strict = false): bool
    {
        if ($strict) {
            return $this->getCountPresent() > ($this->attendanceLimit ?? self::MAX_ATTENDANCE_LIMIT);
        }

        return $this->getCountPresent() >= ($this->attendanceLimit ?? self::MAX_ATTENDANCE_LIMIT);
    }
}
