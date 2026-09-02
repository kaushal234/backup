<?php

declare(strict_types=1);

namespace App\Entity\MinutesOfMeeting;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\DataProcessor\TaskActionDataProcessor;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Dto\TaskInput;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            securityPostDenormalize: "is_granted('MEETING_WRITE_VOTER', object)",
            input: TaskInput::class,
            validate: true,
            processor: TaskActionDataProcessor::class,
        ),
        new Get(security: "is_granted('MEETING_READ_VOTER', object.getMeeting())"),
        new Put(
            denormalizationContext: ['groups' => ['action:edit']],
            security: "is_granted('MEETING_WRITE_VOTER', object.getMeeting()) or is_granted('ACTION_WRITE_VOTER', object)",
        ),
    ],
    routePrefix: 'minutes_of_meeting',
    normalizationContext: ['groups' => ['action', 'customer_public']],
    denormalizationContext: [],
)]
#[ORM\Table(name: 'meeting_actions')]
#[ApiFilter(SearchFilter::class, properties: ['task' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt' => 'DESC'])]
class Action
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['action', 'meeting:detail', 'action:edit'])]
    #[Transferable(conditions: ['completed' => false])]
    private ?People $assignee = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['action', 'action:edit', 'meeting:detail'])]
    #[Assert\Type(type: 'boolean')]
    #[Assert\NotNull]
    private bool $completed = false;

    #[ORM\Column(type: 'text')]
    #[Groups(['action', 'meeting:detail'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by')]
    #[Groups(['action', 'meeting:detail'])]
    #[Gedmo\Blameable(on: 'create')]
    private ?People $createdBy = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['action', 'meeting:detail'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['action', 'meeting:detail'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $initialDueDate;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['action', 'meeting:detail'])]
    private bool $internal = true;

    #[ORM\Column(type: 'integer')]
    #[Groups(['action', 'meeting:detail'])]
    private int $task;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\MinutesOfMeeting\Meeting', inversedBy: 'actions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['action'])]
    private ?Meeting $meeting = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['action', 'action:edit', 'meeting:detail'])]
    private ?string $closingComment = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[Groups(['action', 'meeting:detail'])]
    private ?Customer $customer = null;

    public function reset(): void
    {
        $this->id = null;
        $this->meeting = null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAssignee(): People
    {
        return $this->assignee;
    }

    public function setAssignee(People $assignee): self
    {
        $this->assignee = $assignee;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(Meeting $meeting): self
    {
        $this->meeting = $meeting;

        return $this;
    }

    public function getCreatedBy(): People
    {
        return $this->createdBy;
    }

    public function setCreatedBy(People $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function getInitialDueDate(): \DateTimeInterface
    {
        return $this->initialDueDate;
    }

    public function setInitialDueDate(\DateTime $initialDueDate): self
    {
        $this->initialDueDate = $initialDueDate;

        return $this;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): self
    {
        $this->internal = $internal;

        return $this;
    }

    public function getTask(): int
    {
        return $this->task;
    }

    public function setTask(int $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getClosingComment(): ?string
    {
        return $this->closingComment;
    }

    public function setClosingComment(?string $closingComment): self
    {
        $this->closingComment = $closingComment;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
