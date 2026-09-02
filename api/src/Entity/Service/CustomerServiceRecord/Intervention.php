<?php

declare(strict_types=1);

namespace App\Entity\Service\CustomerServiceRecord;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Directory\People;
use App\Entity\UpdatableStatusEntityInterface;
use App\Validator\Constraints as AppAssert;
use App\Validator\Service\CompleteInterventionGroupsGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[AppAssert\Service\OperatorOnOpenCustomerServiceRecords]
#[ORM\Table(name: 'intervention')]
#[ApiFilter(SearchFilter::class, properties: ['customerServiceRecord', 'leader'])]
#[Gedmo\SoftDeleteable]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(denormalizationContext: ['groups' => ['intervention:update']]),
        new Delete(),
    ],
    routePrefix: 'service',
    normalizationContext: ['groups' => ['intervention', 'user']],
    denormalizationContext: ['groups' => ['intervention:create', 'intervention:detail', 'intervention:update']],
    validationContext: ['groups' => CompleteInterventionGroupsGenerator::class],
)]
#[Loggable]
#[AppAssert\Service\CompleteIntervention(groups: ['Completed'])]
#[Assert\When(
    expression: 'null === this.getId()',
    constraints: [new AppAssert\Service\CreationIntervention()],
)]
class Intervention implements UpdatableStatusEntityInterface
{
    /** @var string */
    final public const PENDING = 'PENDING';
    /** @var string */
    final public const FAILED_ASSIGNEE = 'FAILED_ASSIGNEE';
    /** @var string */
    final public const STARTED = 'STARTED';
    /** @var string */
    final public const SOLVED = 'SOLVED';
    /** @var string */
    final public const TO_CONTINUE = 'TO_CONTINUE';

    final public const OPENED_STATUSES = [self::PENDING, self::STARTED];
    final public const COMPLETED_STATUSES = [self::SOLVED, self::TO_CONTINUE];

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['intervention', 'intervention:update', 'customer_service_record'])]
    public ?\DateTime $startedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['intervention', 'intervention:update'])]
    #[Assert\When(
        expression: 'null !== this.startedAt',
        constraints: [new Assert\GreaterThanOrEqual(propertyPath: 'startedAt')],
    )]
    #[Assert\When(
        expression: 'null === this.startedAt',
        constraints: [new Assert\Blank()],
    )]
    public ?\DateTime $endedAt = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Groups(['intervention', 'intervention:detail', 'customer_service_record'])]
    public ?People $plannedBy = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['intervention', 'intervention:create', 'customer_service_record'])]
    public \DateTime $plannedAt;

    #[ORM\Column(type: 'datetime')]
    #[Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Timestampable(on: 'update')]
    public ?\DateTime $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $deletedAt = null;

    #[Groups(['intervention', 'intervention:update'])]
    public ?string $comments = null;
    #[ORM\ManyToOne(targetEntity: AbstractCustomerServiceRecord::class, inversedBy: 'interventions')]
    #[Groups(['intervention', 'intervention:create'])]
    #[Assert\NotNull]
    public AbstractCustomerServiceRecord $customerServiceRecord;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['intervention', 'intervention:create', 'customer_service_record'])]
    #[Assert\NotNull]
    #[AppAssert\Service\InterventionLeader]
    public People $leader;

    /**
     * @var ArrayCollection<People>
     */
    #[ORM\ManyToMany(targetEntity: People::class)]
    #[Groups(['intervention', 'intervention:create', 'customer_service_record', 'customer_service_record:update', 'intervention:update'])]
    private Collection $operators;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['intervention', 'intervention:detail', 'customer_service_record', 'intervention:update'])]
    private string $status = self::PENDING;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['intervention'])]
    private ?int $id = null;

    public function __construct()
    {
        $this->operators = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<People>
     */
    public function getOperators(): ArrayCollection|Collection
    {
        return $this->operators;
    }

    public function addOperator(People $operator): self
    {
        if ($this->leader === $operator) {
            return $this;
        }

        $this->operators->add($operator);

        return $this;
    }

    public function removeOperator(People $operator): self
    {
        if ($this->operators->contains($operator)) {
            $this->operators->removeElement($operator);
        }

        return $this;
    }

    public function isOpen(): bool
    {
        return \in_array($this->getStatus(), self::OPENED_STATUSES, true);
    }

    public function isCompleted(): bool
    {
        return \in_array($this->getStatus(), self::COMPLETED_STATUSES, true);
    }
}
