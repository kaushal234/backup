<?php

declare(strict_types=1);

namespace App\Entity\Task;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\Task\TaskCommentController;
use App\Controller\Task\TaskReopenController;
use App\DataProcessor\Task\TaskAutoCreationProcessor;
use App\Doctrine\Mapping\Attributes as App;
use App\Dto\Task\TaskBatchCreation;
use App\Entity\BaseTask;
use App\Entity\ConfidentialInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\Task\AcceptedModule;
use App\Validator\Constraints\Task\AcceptedReferenceId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['base_task', 'task', 'module_light', 'people_public', 'location_public']]
        ),
        new Post(
            validationContext: ['groups' => ['Default', 'creation']],
            name: 'create_task',
        ),
        new Post(
            uriTemplate: 'tasks/batch',
            denormalizationContext: ['groups' => ['batch_task:write']],
            input: TaskBatchCreation::class,
            processor: TaskAutoCreationProcessor::class
        ),
        new Post(
            uriTemplate: '/tasks/{id}/comment',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TaskCommentController::class,
            denormalizationContext: ['groups' => ['task:comment']],
            validationContext: ['groups' => ['task:comment']],
            name: 'task_comment',
        ),
        new Post(
            uriTemplate: '/tasks/{id}/transfer',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TaskCommentController::class,
            denormalizationContext: ['groups' => ['task:comment', 'task:transfer', 'task:reschedule']],
            security: 'is_granted("TASK_TRANSFER_VOTER", object)',
            validationContext: ['groups' => ['task:comment', 'task:reschedule']],
            name: 'task_transfer',
        ),
        new Post(
            uriTemplate: '/tasks/{id}/reschedule',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TaskCommentController::class,
            denormalizationContext: ['groups' => ['task:comment', 'task:reschedule']],
            security: 'is_granted("TASK_TRANSFER_VOTER", object)',
            validationContext: ['groups' => ['task:comment', 'task:reschedule']],
            name: 'task_reschedule',
        ),
        new Post(
            uriTemplate: '/tasks/{id}/pause',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TaskCommentController::class,
            denormalizationContext: ['groups' => ['task:comment']],
            security: 'is_granted("FEATURE_PAUSE_UNPAUSE_TASK")',
            validationContext: ['groups' => ['task:comment']],
            name: 'task_pause_comment',
        ),
        new Post(
            uriTemplate: '/tasks/{id}/close',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TaskCommentController::class,
            denormalizationContext: ['groups' => ['task:comment']],
            security: 'is_granted("TASK_WRITE_VOTER", object)',
            validationContext: ['groups' => ['task:comment']],
            name: 'task_close_comment',
        ),
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['task:edit', 'base_task:edit']],
            security: 'is_granted("TASK_WRITE_VOTER", object)',
        ),
        new Post(
            uriTemplate: '/tasks/{id}/reopen',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TaskReopenController::class,
            denormalizationContext: ['groups' => ['task:comment']],
            security: 'is_granted("TASK_REOPEN_VOTER", object)',
            validationContext: ['groups' => ['task:comment']],
            name: 'task_reopen',
        ),
        new Get(
            uriTemplate: '/tasks/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TaskFile::class),
                'id' => new Link(fromClass: Task::class),
            ],
            defaults: ['parentProperty' => 'task', 'class' => TaskFile::class],
            controller: DownloadController::class,
            name: 'download_task_file'
        ),
        new Post(
            uriTemplate: '/tasks/{id}/files',
            defaults: ['method' => 'getFiles', 'class' => TaskFile::class],
            controller: UploadController::class,
            security: 'is_granted("TASK_TRANSFER_VOTER", object)',
            deserialize: false,
            name: 'upload_task_file'
        ),
        new Delete(
            uriTemplate: '/tasks/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TaskFile::class),
                'id' => new Link(fromClass: Task::class),
            ],
            defaults: ['parentProperty' => 'task', 'class' => TaskFile::class],
            controller: DeleteController::class,
            security: 'is_granted("TASK_WRITE_VOTER", object)',
            name: 'delete_task_file'
        ),
    ],
    normalizationContext: ['groups' => ['base_task', 'task', 'task:item', 'module_light', 'people_public', 'people_photo', 'location_public', 'file']],
    denormalizationContext: ['groups' => ['task:write', 'base_task:write']],
)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id', 'module.name', 'createdBy.lastname', 'createdBy.firstname', 'assignee.lastname', 'assignee.firstname', 'indiceFactor', 'status'])]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'module.name', 'createdBy.lastname', 'assignee.lastname', 'indiceFactor', 'startedAt', 'dueDate', 'status'])]
#[ApiFilter(SearchFilter::class, properties: ['status', 'module', 'module.name', 'referenceId', 'createdBy', 'assignee', 'indiceFactor', 'legacyId', 'location' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['startedAt' => 'exact', 'dueDate' => 'exact'])]
#[ApiFilter(ColumnsFilter::class)]
#[Legacy\Synchronize(table: 'tasks', forceUpdate: true)]
#[ORM\Entity]
#[App\Loggable]
#[AcceptedModule]
#[AcceptedReferenceId]
class Task extends BaseTask implements ConfidentialInterface, LegacyIdInterface, \Stringable
{
    final public const string WHT = 'WHT';

    final public const CLOSED = 'CLOSED';

    final public const PAUSE = 'PAUSE';

    final public const UNIT = ['DAYS', 'WEEKS', 'MONTHS', 'YEARS'];

    #[Legacy\Id]
    public ?int $legacyId = 0;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['task:item', 'task:write', 'task:edit'])]
    public bool $confidential = false;

    #[Groups(['task', 'task:write', 'task:dueDate'])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\GreaterThan('today', groups: ['creation'])]
    #[Legacy\Column(column: 'due_date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTimeInterface $dueDate = null;

    #[Groups(['task:write', 'task:edit', 'task', 'task:item'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'module', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public ?Module $module;

    #[Groups(['task:write', 'task:edit', 'task', 'task:item'])]
    #[Assert\Choice(choices: [self::IF_1, self::IF_10, self::IF_100, self::IF_1000, self::IF_10000])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'ifactor')]
    public ?string $indiceFactor = self::IF_1;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\GreaterThan(0)]
    #[Groups(['task:write', 'task:edit', 'task', 'task:item', 'task_light'])]
    public int $referenceId;

    #[Legacy\Column(column: 'date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public \DateTimeInterface $createdAt;

    #[Groups(['task:item'])]
    #[Legacy\Column(column: 'dt_closed', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTimeInterface $closedAt = null;

    #[Groups(['task:write', 'task:item'])]
    #[Legacy\Column(column: 'assignor', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?People $createdBy;

    #[Groups(['task:write', 'task:edit', 'task', 'task:item', 'task:transfer'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'assignee', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?People $assignee;

    #[Groups(['task:write', 'task:edit', 'task', 'task:item',  'task_light'])]
    #[Assert\NotNull]
    #[Assert\LessThanOrEqual(propertyPath: 'dueDate')]
    public ?\DateTimeInterface $startedAt;

    #[Groups(['task:write', 'task', 'task:item',  'task_light', 'task:transfer', 'task:reschedule'])]
    #[Assert\GreaterThanOrEqual('today', message: 'This date cannot be in the past.', groups: ['task:reschedule'])]
    public ?\DateTimeInterface $rescheduleDate = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['task:write', 'task:edit', 'task:item'])]
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    #[Legacy\Column(column: 'escalation_trigger')]
    public int $escalationTrigger = 60;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\Choice(choices: self::UNIT)]
    #[Groups(['task:write', 'task:edit', 'task:item'])]
    #[Assert\NotNull]
    public ?string $escalationTriggerUnit = self::UNIT[0];

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['task', 'task:item'])]
    public ?\DateTimeInterface $escalationDate;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    #[Groups(['task:write', 'task:edit', 'task'])]
    public Location $location;

    #[Groups(['task:comment'])]
    #[Assert\NotNull(groups: ['task:comment'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['task'])]
    public ?string $lastComment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['task:close:comment', 'task:item'])]
    #[Assert\NotNull(groups: ['task:close:comment'])]
    public ?string $closeComment = null;

    #[Legacy\Column(column: 'task')]
    public string $description;

    #[Groups(['task:write', 'task', 'task:item'])]
    protected int $id;

    #[Assert\Choice(choices: [self::PENDING, self::IN_PROGRESS, self::CLOSED, self::PAUSE])]
    #[Groups(['task:write', 'task', 'task:item', 'task:status'])]
    #[Legacy\Column(column: 'status')]
    protected string $status = self::PENDING;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: People::class)]
    #[ORM\JoinTable(name: 'task_recipients')]
    #[Groups(['task:write', 'task:edit', 'task:item', 'task:comment'])]
    private Collection $recipients;

    /**
     * @var Collection<TaskFile>
     */
    #[ORM\OneToMany(targetEntity: TaskFile::class, mappedBy: 'task', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['task:write', 'task:item'])]
    private Collection $files;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return \sprintf('%s - %s', $this->id, $this->shortDescription);
    }

    /**
     * @return Collection<People>
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(People $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients->add($recipient);
        }

        return $this;
    }

    public function removeRecipient(People $recipient): self
    {
        $this->recipients->removeElement($recipient);

        return $this;
    }

    public function getFiles(): ArrayCollection|Collection
    {
        return $this->files;
    }

    public function addFile(TaskFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setTask($this);
        }

        return $this;
    }

    public function removeFile(TaskFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    public function isConfidential(): bool
    {
        return $this->confidential;
    }

    public function getLegacyId(): ?int
    {
        return $this->legacyId;
    }
}
