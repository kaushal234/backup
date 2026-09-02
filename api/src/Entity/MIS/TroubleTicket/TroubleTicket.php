<?php

declare(strict_types=1);

namespace App\Entity\MIS\TroubleTicket;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
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
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\MIS\TroubleTicketReopenController;
use App\Controller\MIS\TroubleTicketTransferController;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\BaseTask;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\ColumnsFilter;
use App\Filter\MIS\TroubleTicketOpenFilter;
use App\Filter\SimpleSearchFilter;
use App\Jira\Resources\IssueInterface;
use App\Jira\Resources\TroubleTicketIssue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\MIS\TroubleTicketRepository")]
#[ORM\Table]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['trouble_ticket', 'people_public', 'people_photo', 'location_public', 'module', 'category', 'application', 'type', 'region:list', 'support_level']],
        ),
        new Post(denormalizationContext: ['groups' => ['trouble_ticket:create']]),
        new Post(
            uriTemplate: '/trouble_tickets/{id}/transfer',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TroubleTicketTransferController::class,
            denormalizationContext: ['groups' => ['trouble_ticket:transfer']],
            security: "is_granted('FEATURE_TROUBLE_TICKET_TRANSFER_VOTER', object)",
            name: 'transfer_trouble_ticket',
        ),
        new Post(
            uriTemplate: '/trouble_tickets/{id}/reopen',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TroubleTicketReopenController::class,
            denormalizationContext: ['groups' => ['trouble_ticket:comment']],
            security: "is_granted('FEATURE_TROUBLE_TICKET_REOPEN_VOTER', object)",
            name: 'reopen_trouble_ticket',
        ),
        new Post(
            uriTemplate: '/trouble_tickets/{id}/comment',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: TroubleTicketTransferController::class,
            denormalizationContext: ['groups' => ['trouble_ticket:comment']],
            security: "is_granted('FEATURE_TROUBLE_TICKET_COMMENT_VOTER', object)",
            name: 'comment_trouble_ticket',
        ),
        new Post(
            uriTemplate: '/trouble_tickets/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => TroubleTicketFile::class],
            controller: UploadController::class,
            security: 'is_granted("FEATURE_TROUBLE_TICKET_UPLOAD_FILE_VOTER", object)',
            deserialize: false,
            name: 'upload_trouble_ticket_files'
        ),
        new Put(),
        new Put(
            uriTemplate: '/trouble_tickets/{id}/status',
            denormalizationContext: ['groups' => ['trouble_ticket:update_status']],
            name: 'update_trouble_ticket_status',
        ),
        new Delete(
            uriTemplate: '/trouble_tickets/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TroubleTicketFile::class),
                'id' => new Link(fromClass: TroubleTicket::class),
            ],
            defaults: ['parentProperty' => 'troubleTicket', 'class' => TroubleTicketFile::class],
            controller: DeleteController::class,
            security: 'is_granted("FEATURE_TROUBLE_TICKET_DELETE_FILE")',
            name: 'delete_trouble_ticket_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/trouble_tickets/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TroubleTicketFile::class),
                'id' => new Link(fromClass: TroubleTicket::class),
            ],
            defaults: ['parentProperty' => 'troubleTicket', 'class' => TroubleTicketFile::class],
            controller: DownloadController::class,
            name: 'download_trouble_ticket_file',
        ),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => self::ITEM_NORMALIZATION_GROUPS],
    denormalizationContext: ['groups' => ['trouble_ticket:edit_partial']],
)]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'createdAt',
    'lastCommentedAt',
    'dueDate',
    'type.type',
    'status',
    'jiraIssueNumber',
    'indiceFactor',
    'type.description',
    'module.name',
    'isAddToUserStories',
    'createdBy.lastname',
    'assignee.lastname',
    'misAssignee.lastname',
    'createdBy.businessUnit.region.name',
    'supportLevel.level',
])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['subscription', 'workflow']])]
#[ApiFilter(BooleanFilter::class, properties: ['isAddToUserStories', 'autoEscalated'])]
#[ApiFilter(SearchFilter::class, properties: [
    'id',
    'legacyId',
    'createdBy',
    'status',
    'createdBy.businessUnit.location',
    'createdBy.businessUnit.region',
    'createdBy.businessUnit.region.subDivision',
    'createdBy.businessUnit.region.subDivision.division',
    'createdBy.premise.supportTeam',
    'module.application',
    'module.operationalOwner',
    'type',
    'type.type',
    'module',
    'assignee',
    'createdBy.premise',
    'indiceFactor',
    'misAssignee',
    'jiraIssueNumber',
    'supportLevel',
    'supportLevel.level',
    'shortDescription' => 'partial',
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
    'legacyId' => 'partial',
    'jiraIssueNumber' => 'partial',
    'misAssignee.firstname' => 'partial',
    'misAssignee.lastname' => 'partial',
    'createdBy.businessUnit.location.name' => 'partial',
    'createdBy.businessUnit.region.name' => 'partial',
    'createdBy.businessUnit.region.subDivision.name' => 'partial',
    'createdBy.businessUnit.region.subDivision.division.name' => 'partial',
    'createdBy.premise.supportTeam.name' => 'partial',
    'module.application.name' => 'partial',
    'module.operationalOwner.firstname' => 'partial',
    'module.operationalOwner.lastname' => 'partial',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact', 'dueDate' => 'exact', 'lastCommentedAt' => 'exact'])]
#[ApiFilter(ExistsFilter::class, properties: ['jiraIssueNumber', 'assignee', 'misAssignee'])]
#[ApiFilter(TroubleTicketOpenFilter::class)]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[App\Audible(type: 'trouble_ticket')]
class TroubleTicket extends BaseTask implements UpdatableStatusEntityInterface, IssueInterface
{
    /** @var array */
    final public const ITEM_NORMALIZATION_GROUPS = ['trouble_ticket', 'trouble_ticket:item', 'people_public', 'people_photo', 'location_public', 'issue', 'module', 'category', 'application', 'type', 'file', 'module:local_key_users', 'priority', 'trouble_ticket:user_story:add', 'support_level'];

    /** @var string */
    final public const PENDING_MOO = 'PENDING MOO/GKU';

    /** @var string */
    final public const AWAITING_USER = 'AWAITING USER';
    /** @var string */
    final public const AWAITING_USER_MOO = 'MOO/GKU AWAITING USER';

    /** @var string */
    final public const SOLUTION_PROPOSED = 'SOLUTION PROPOSED';
    /** @var string */
    final public const SOLUTION_PROPOSED_MOO = 'MOO/GKU SOLUTION PROPOSED';

    /** @var string */
    final public const CLOSED_SOLVED = 'SOLVED';

    /** @var string */
    final public const CLOSED_NOT_AN_ISSUE = 'NOT AN ISSUE';

    /** @var string */
    final public const CLOSED_ALREADY_RAISED = 'ALREADY RAISED';

    /** @var string */
    final public const CLOSED_NOT_APPROVED = 'NOT APPROVED';

    /** @var string */
    final public const NOT_SATISFIED_AT_ALL = 'Not satisfied at all';

    /** @var string */
    final public const NOT_MUCH_SATISFIED = 'Not much satisfied';

    /** @var string */
    final public const SATISFIED = 'Satisfied';

    /** @var string */
    final public const VERY_SATISFIED = 'Very satisfied';

    final public const CLOSED_STATUSES = [self::CLOSED_ALREADY_RAISED, self::CLOSED_SOLVED, self::CLOSED_NOT_AN_ISSUE, self::CLOSED_NOT_APPROVED];
    final public const OPEN_STATUSES = [self::PENDING, self::PENDING_MOO, self::IN_PROGRESS, self::AWAITING_USER_MOO, self::AWAITING_USER, self::SOLUTION_PROPOSED, self::SOLUTION_PROPOSED_MOO];

    #[Groups(['trouble_ticket', 'trouble_ticket:on_behalf', 'trouble_ticket:reminder'])]
    public ?People $createdBy = null;

    #[Groups(['trouble_ticket', 'trouble_ticket:transfer', 'trouble_ticket:edit', 'trouble_ticket:reminder'])]
    public ?People $assignee = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['trouble_ticket', 'trouble_ticket:edit'])]
    #[Transferable(handler: 'handler.base_task')]
    public ?People $misAssignee = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\MIS\TroubleTicket\Type')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['trouble_ticket', 'trouble_ticket:edit', 'trouble_ticket:create'])]
    public Type $type;

    #[ORM\ManyToOne(targetEntity: SupportLevel::class)]
    #[Groups(['trouble_ticket', 'trouble_ticket:edit'])]
    public ?SupportLevel $supportLevel = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['trouble_ticket', 'trouble_ticket:item', 'trouble_ticket:reminder'])]
    public ?string $jiraIssueNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['trouble_ticket:item', 'trouble_ticket:create', 'trouble_ticket:edit'])]
    public ?string $url;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['trouble_ticket:item', 'trouble_ticket:create'])]
    public ?string $referer;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['trouble_ticket:item', 'trouble_ticket:create'])]
    public ?string $hostName;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::NOT_SATISFIED_AT_ALL, self::NOT_MUCH_SATISFIED, self::SATISFIED, self::VERY_SATISFIED])]
    #[Groups(['trouble_ticket:item', 'trouble_ticket:edit_partial', 'trouble_ticket:comment'])]
    public ?string $satisfaction = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['trouble_ticket:edit_partial', 'trouble_ticket:comment'])]
    public ?string $satisfactionComment = null;

    #[Groups(['trouble_ticket', 'trouble_ticket:edit'])]
    public ?\DateTimeInterface $dueDate = null;

    #[Groups(['trouble_ticket'])]
    public \DateTimeInterface $createdAt;

    #[Groups(['trouble_ticket:item'])]
    public ?\DateTimeInterface $closedAt = null;

    #[Groups(['trouble_ticket:item'])]
    public ?TroubleTicketIssue $issue = null;

    #[Groups(['trouble_ticket:comment', 'trouble_ticket:transfer', 'trouble_ticket:edit_partial'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['trouble_ticket:item'])]
    public ?\DateTimeInterface $solutionProposedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['trouble_ticket'])]
    public ?\DateTimeInterface $lastCommentedAt = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['trouble_ticket', 'trouble_ticket:user_story:add'])]
    public bool $isAddToUserStories = false;

    #[Groups(['trouble_ticket', 'trouble_ticket:create', 'trouble_ticket:edit', 'trouble_ticket:reminder'])]
    public string $shortDescription;

    #[Groups(['trouble_ticket', 'trouble_ticket:create'])]
    public string $description;

    #[Assert\Choice(choices: [self::IF_1, self::IF_10, self::IF_100, self::IF_1000])]
    #[Groups(['trouble_ticket', 'trouble_ticket:create', 'trouble_ticket:edit', 'trouble_ticket:reminder'])]
    public ?string $indiceFactor = null;

    #[Groups(['trouble_ticket', 'trouble_ticket:edit', 'trouble_ticket:create', 'trouble_ticket:reminder'])]
    #[Assert\NotNull]
    public ?Module $module = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['trouble_ticket:item'])]
    public bool $autoEscalated = false;

    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTimeInterface $autoEscalatedAt = null;

    #[Groups(['trouble_ticket', 'trouble_ticket:create_from', 'trouble_ticket:reminder'])]
    protected int $id;

    #[Assert\Choice(choices: [self::PENDING, self::PENDING_MOO, self::AWAITING_USER_MOO, self::IN_PROGRESS, self::AWAITING_USER, self::SOLUTION_PROPOSED, self::SOLUTION_PROPOSED_MOO, self::CLOSED_NOT_AN_ISSUE, self::CLOSED_ALREADY_RAISED, self::CLOSED_SOLVED, self::CLOSED_NOT_APPROVED])]
    #[Groups(['trouble_ticket', 'trouble_ticket:edit_partial', 'trouble_ticket:update_status', 'trouble_ticket:reminder'])]
    protected string $status = self::PENDING;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinTable(name: 'trouble_ticket_ccs')]
    #[Assert\All([new Assert\Type(type: People::class)])]
    #[Groups(['trouble_ticket:item', 'trouble_ticket:comment', 'trouble_ticket:transfer', 'trouble_ticket:create'])]
    private Collection $ccs;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinTable(name: 'trouble_ticket_additional_owners')]
    #[Assert\All([new Assert\Type(type: People::class)])]
    #[Groups(['trouble_ticket', 'trouble_ticket:edit_partial'])]
    private Collection $additionalOwners;

    /**
     * @var Collection<TroubleTicketFile>
     */
    #[ORM\OneToMany(mappedBy: 'troubleTicket', targetEntity: 'App\Entity\MIS\TroubleTicket\TroubleTicketFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['trouble_ticket:item'])]
    private Collection $files;

    public function __construct()
    {
        $this->ccs = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->additionalOwners = new ArrayCollection();
    }

    /**
     * @return Collection<People>
     */
    public function getCcs()
    {
        return $this->ccs;
    }

    public function addCc(People $ccs): self
    {
        $this->ccs->add($ccs);

        return $this;
    }

    public function removeCc(People $ccs): self
    {
        $this->ccs->removeElement($ccs);

        return $this;
    }

    /**
     * @return Collection<People>
     */
    public function getAdditionalOwners()
    {
        return $this->additionalOwners;
    }

    public function addAdditionalOwner(People $additionalOwner): self
    {
        $this->additionalOwners->add($additionalOwner);

        return $this;
    }

    public function removeAdditionalOwner(People $additionalOwner): self
    {
        $this->additionalOwners->removeElement($additionalOwner);

        return $this;
    }

    /**
     * @return Collection<TroubleTicketFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(TroubleTicketFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setTroubleTicket($this);
        }

        return $this;
    }

    public function removeFile(TroubleTicketFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    public function getProjectNumber(): int
    {
        return $this->module->getApplication()->jiraProjectId;
    }

    public function getAuditType(): string
    {
        return 'trouble_ticket';
    }

    public function getAudibleProperties(): array
    {
        return ['status'];
    }
}
