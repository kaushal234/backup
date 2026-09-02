<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\Module\Module;
use App\Entity\Task\PartNumberTask;
use App\Entity\Task\RenewGuestUser;
use App\Entity\Task\Task;
use App\Entity\Task\Template;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'csv' => ['text/csv'], 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['base_task', 'trouble_ticket', 'task', 'module_light', 'people_public']]
        ),
    ],
)]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'discr', type: 'string')]
#[DiscriminatorMap([
    'task' => Task::class,
    'trouble_ticket' => TroubleTicket::class,
    'renew_guest_user' => RenewGuestUser::class,
    'part_number_task' => PartNumberTask::class,
])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'partial',
    'module.name' => 'partial',
    'createdBy.firstname' => 'partial',
    'createdBy.lastname' => 'partial',
    'assignee.firstname' => 'partial',
    'assignee.lastname' => 'partial',
    'shortDescription' => 'partial',
    'description' => 'partial',
    'status' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'module.name', 'createdBy.lastname', 'assignee.lastname', 'indiceFactor', 'startedAt', 'createdAt', 'dueDate', 'status', 'rescheduleDate'])]
#[ApiFilter(SearchFilter::class, properties: ['status', 'module', 'createdBy', 'assignee', 'indiceFactor', 'shortDescription', 'description'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact', 'dueDate' => 'exact', 'rescheduleDate' => 'exact'])]
#[ApiFilter(ColumnsFilter::class)]
#[ORM\Entity]
class BaseTask implements UpdatableStatusEntityInterface
{
    final public const string PENDING = 'PENDING';
    final public const string IN_PROGRESS = 'IN PROGRESS';
    final public const string IF_1 = 'IF 1';
    final public const string IF_100 = 'IF 100';
    final public const string IF_10 = 'IF 10';
    final public const string IF_1000 = 'IF 1000';
    final public const string IF_10000 = 'IF 10000';

    #[ORM\ManyToOne(targetEntity: Module::class)]
    #[Groups(['base_task'])]
    public ?Module $module = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    #[Groups(['base_task'])]
    public ?string $indiceFactor = null;

    #[ORM\Column(type: 'datetime')]
    #[Timestampable(on: 'create')]
    #[Groups(['base_task'])]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['base_task'])]
    public ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['base_task'])]
    public ?\DateTimeInterface $rescheduleDate = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Groups(['base_task'])]
    #[Transferable(handler: 'handler.base_task')]
    #[Blameable(on: 'create')]
    public ?People $createdBy = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['base_task'])]
    public ?\DateTimeInterface $closedAt = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'assignee', referencedColumnName: 'id')]
    #[Groups(['base_task'])]
    #[Transferable(handler: 'handler.base_task')]
    public ?People $assignee = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['base_task', 'base_task:write', 'base_task:edit'])]
    public string $shortDescription;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['base_task', 'base_task:write', 'base_task:edit'])]
    public string $description;

    #[ORM\Column(name: 'legacy_id', type: 'integer', nullable: false)]
    public ?int $legacyId = 0;

    #[ORM\ManyToOne(targetEntity: Template::class)]
    public ?Template $template = null;

    #[ORM\Column(type: 'string')]
    protected string $status = self::PENDING;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['base_task'])]
    protected int $id;

    public function getId(): int
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
}
