<?php

declare(strict_types=1);

namespace App\Entity\MIS\Project;

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
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\File\ZipController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\ConfidentialInterface;
use App\Entity\Directory\People;
use App\Entity\Directory\Region;
use App\Entity\Module\Module;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\ColumnsFilter;
use App\Filter\MIS\Project\ActivePhaseEstimatedClosureAtOrderFilter;
use App\Filter\MIS\Project\ActivePhaseRevisedClosureAtOrderFilter;
use App\Filter\MIS\Project\DueDateOrderFilter;
use App\Filter\MIS\Project\RevisedDueDateOrderFilter;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\MIS\Project\ProjectTaskOpen;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['project', 'module_public', 'business_unit_public', 'people_public', 'phase', 'active_phase', 'task:status', 'tag', 'region_light']],
        ),
        new Post(
            security: 'is_granted("FEATURE_MIS_PROJECT_WRITE")',
        ),
        new Post(
            uriTemplate: '/projects/{id}/status',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            denormalizationContext: ['groups' => ['project:comment', 'project:status']],
            security: "is_granted('PROJECT_EDIT_VOTER', object)",
            validationContext: ['groups' => ['project:comment', 'project:status']],
            name: 'mis_project_status',
        ),
        new Post(
            uriTemplate: '/projects/{id}/comment',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            denormalizationContext: ['groups' => ['project:comment']],
            security: "is_granted('PROJECT_COMMENT_VOTER', object)",
            validationContext: ['groups' => ['project:comment']],
            name: 'mis_project_comment',
        ),
        new Post(
            uriTemplate: '/projects/{id}/close',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            denormalizationContext: ['groups' => ['project:close', 'project:status']],
            security: "is_granted('PROJECT_CLOSE_VOTER', object)",
            validationContext: ['groups' => ['project:close', 'project:status']],
            name: 'mis_project_close',
        ),
        new Get(
            order: ['phases.number']
        ),
        new Put(
            denormalizationContext: ['groups' => ['project:edit', 'phase:edit']],
            security: "is_granted('PROJECT_EDIT_VOTER', object)",
        ),
        new Delete(
            uriTemplate: '/projects/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ProjectFile::class),
                'id' => new Link(fromClass: Project::class),
            ],
            defaults: ['parentProperty' => 'project', 'class' => ProjectFile::class],
            controller: DeleteController::class,
            security: 'is_granted("FEATURE_MIS_PROJECT_FILE_DELETE")',
            name: 'mis_project_delete_file',
        ),
        new Get(
            uriTemplate: '/projects/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ProjectFile::class),
                'id' => new Link(fromClass: Project::class),
            ],
            defaults: ['parentProperty' => 'project', 'class' => ProjectFile::class],
            controller: DownloadController::class,
            security: 'is_granted("PROJECT_FILE_DOWNLOAD_VOTER", object)',
            name: 'mis_project_download_file'
        ),
        new Get(
            uriTemplate: '/projects/{id}/files',
            formats: ['zip' => ['application/zip']],
            controller: ZipController::class,
            name: 'mis_project_zip_files'
        ),
        new Post(
            uriTemplate: '/projects/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => ProjectFile::class],
            controller: UploadController::class,
            security: 'is_granted("PROJECT_FILE_UPLOAD_VOTER", object)',
            deserialize: false,
            name: 'mis_project_upload_file',
        ),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => self::ITEM_NORMALIZATION_GROUPS],
    denormalizationContext: ['groups' => ['project:create', 'phase:create']],
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name', 'indicesFactor', 'createdAt', 'startedAt', 'module.name', 'region.name', 'projectManager.lastname', 'misOwner.lastname', 'status'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['workflow', 'file']])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId', 'status', 'projectManager', 'misOwner', 'indicesFactor', 'module', 'misMembers', 'moduleKeyUsers', 'region', 'tags'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'startedAt'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial', 'description' => 'partial', 'module.name', 'misOwner.lastname', 'misOwner.firstname', 'projectManager.lastname', 'projectManager.firstname', 'id', 'indicesFactor', 'region.name', 'status'])]
#[ApiFilter(ActivePhaseEstimatedClosureAtOrderFilter::class)]
#[ApiFilter(ActivePhaseRevisedClosureAtOrderFilter::class)]
#[ApiFilter(DueDateOrderFilter::class)]
#[ApiFilter(RevisedDueDateOrderFilter::class)]
#[ApiFilter(ColumnsFilter::class)]
#[ORM\Table(name: 'mis_projects')]
#[ORM\Entity]
#[App\Loggable]
#[ProjectTaskOpen(groups: ['project:status'])]
class Project implements UpdatableStatusEntityInterface, ConfidentialInterface, LegacyIdInterface
{
    public const array INDICES_FACTOR = [self::IF1, self::IF10, self::IF100, self::IF1000, self::IF10000];
    public const string IF1 = 'IF 1';
    public const string IF10 = 'IF 10';
    public const string IF100 = 'IF 100';
    public const string IF1000 = 'IF 1000';
    public const string IF10000 = 'IF 10000';
    public const array PHASES_STATUSES = [self::PHASE_0, self::PHASE_1, self::PHASE_2, self::PHASE_3, self::PHASE_4];
    public const array ITEM_NORMALIZATION_GROUPS = ['project', 'project:item', 'module_public', 'business_unit_public', 'people_public', 'file', 'people_photo', 'phase', 'tag', 'active_phase', 'region_light'];
    public const string PENDING = 'PENDING';
    public const string PHASE_0 = 'PHASE 0';
    public const string PHASE_1 = 'PHASE 1';
    public const string PHASE_2 = 'PHASE 2';
    public const string PHASE_3 = 'PHASE 3';
    public const string PHASE_4 = 'PHASE 4';
    public const string CANCELLED = 'CANCELLED';
    public const string CLOSED = 'CLOSED';

    #[ORM\Column(type: 'string')]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['project', 'project:create', 'project:edit'])]
    public string $name;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['project:item', 'project:create', 'project:edit'])]
    public string $description;

    #[ORM\ManyToOne(targetEntity: Region::class)]
    #[Assert\NotNull]
    #[Groups(['project', 'project:create', 'project:edit'])]
    public ?Region $region = null;

    #[ORM\Column(type: 'string')]
    #[Assert\Type(type: 'string')]
    #[Assert\NotNull]
    #[Assert\Choice(choices: self::INDICES_FACTOR)]
    #[Groups(['project', 'project:create', 'project:edit'])]
    public string $indicesFactor;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Assert\NotNull]
    #[Groups(['project', 'project:create', 'project:edit_manager'])]
    public ?People $projectManager = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['project', 'project:create', 'project:edit_owner'])]
    public ?People $misOwner = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['project'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    public \DateTimeInterface $startedAt;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['project', 'project:create', 'project:edit'])]
    public bool $confidential = false;

    #[ORM\ManyToOne(targetEntity: Module::class)]
    #[Groups(['project', 'project:create', 'project:edit'])]
    public ?Module $module = null;

    #[Groups(['project:comment'])]
    #[Assert\NotNull(groups: ['project:comment'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotNull(groups: ['project:close'])]
    #[Groups(['project:item', 'project:close'])]
    public ?string $conclusion = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Url(
        message: 'The url {{ value }} is not a valid url',
        protocols: ['https'],
        requireTld: true,
    )]
    #[Groups(['project', 'project:create', 'project:edit'])]
    public ?string $teamsLink = null;

    #[Gedmo\Timestampable(on: 'change', field: ['lastComment'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['project'])]
    public ?\DateTimeInterface $lastCommentedAt = null;

    #[ORM\Column(name: 'last_comment', type: 'text', nullable: true)]
    #[Groups(['project'])]
    public ?string $lastComment = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull(groups: ['project:status'])]
    #[Assert\Choice(choices: [self::PENDING, self::PHASE_0, self::PHASE_1, self::PHASE_2, self::PHASE_3, self::PHASE_4, self::CANCELLED, self::CLOSED], strict: true, groups: ['Default', 'project:status'])]
    #[Groups(['project', 'project:status'])]
    protected string $status = self::PENDING;

    #[ORM\Column(name: 'legacy_id', type: 'integer', nullable: false)]
    private ?int $legacyId = 0;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['project'])]
    private int $id;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: People::class)]
    #[ORM\JoinTable(name: 'mis_project_module_key_users_people')]
    #[Assert\All([new Assert\Type(type: People::class)])]
    #[Groups(['project:create', 'project:item', 'project:edit'])]
    private Collection $moduleKeyUsers;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: People::class)]
    #[ORM\JoinTable(name: 'mis_project_mis_members_people')]
    #[Assert\All([new Assert\Type(type: People::class)])]
    #[Groups(['project:create', 'project:item', 'project:edit'])]
    private Collection $misMembers;

    /**
     * @var Collection<ProjectFile>
     */
    #[ORM\OneToMany(targetEntity: ProjectFile::class, mappedBy: 'project', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['project:item'])]
    private Collection $files;

    /**
     * @var Collection<Phase>
     */
    #[ORM\OneToMany(targetEntity: Phase::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['project', 'project:create', 'project:edit'])]
    private Collection $phases;

    /**
     * @var Collection<ProjectTag>
     */
    #[ORM\ManyToMany(targetEntity: ProjectTag::class, mappedBy: 'projects')]
    #[Groups(['project', 'project:create', 'project:edit'])]
    private Collection $tags;

    public function __construct()
    {
        $this->misMembers = new ArrayCollection();
        $this->moduleKeyUsers = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->phases = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleKeyUsers(): ArrayCollection|Collection
    {
        return $this->moduleKeyUsers;
    }

    public function addModuleKeyUser(People $moduleKeyUser): self
    {
        $this->moduleKeyUsers->add($moduleKeyUser);

        return $this;
    }

    public function removeModuleKeyUser(People $moduleKeyUser): self
    {
        $this->moduleKeyUsers->removeElement($moduleKeyUser);

        return $this;
    }

    public function getMisMembers(): ArrayCollection|Collection
    {
        return $this->misMembers;
    }

    public function addMisMember(People $misMember): self
    {
        $this->misMembers->add($misMember);

        return $this;
    }

    public function removeMisMember(People $misMember): self
    {
        $this->misMembers->removeElement($misMember);

        return $this;
    }

    public function getFiles(): ArrayCollection|Collection
    {
        return $this->files;
    }

    public function addFile(ProjectFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setProject($this);
        }

        return $this;
    }

    public function removeFile(ProjectFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    /**
     * @return Collection<Phase>
     */
    public function getPhases(): Collection
    {
        return $this->phases;
    }

    public function addPhase(Phase $phase): self
    {
        if (!$this->phases->contains($phase)) {
            $this->phases->add($phase);
            $phase->project = $this;
        }

        return $this;
    }

    public function removePhase(Phase $phase): self
    {
        if ($this->phases->contains($phase)) {
            $this->phases->removeElement($phase);
        }

        return $this;
    }

    /**
     * @return Collection<ProjectTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ProjectTag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addProject($this);
        }

        return $this;
    }

    public function removeTag(ProjectTag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeProject($this);
        }

        return $this;
    }

    /**
     * @return Collection<ProjectFile>
     */
    public function getZippableFiles(): Collection
    {
        return $this->getFiles();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): object
    {
        $this->status = $status;

        return $this;
    }

    #[Groups('active_phase')]
    public function getActivePhase(): ?Phase
    {
        $activePhases = $this->getPhases()->filter(static fn (Phase $phase) => true === $phase->isActive());

        return $activePhases->isEmpty() ? null : $activePhases->first();
    }

    #[Groups('project')]
    public function getActivePhaseEstimatedClosureAt(): ?string
    {
        return $this->getActivePhase()?->estimatedClosureAt?->format('d.m.Y');
    }

    #[Groups('project')]
    public function getActivePhaseRevisedClosureAt(): ?string
    {
        return $this->getActivePhase()?->revisedClosureAt?->format('d.m.Y');
    }

    #[Groups('project')]
    public function getDueDate(): ?string
    {
        return $this->getPhaseByNumber(4)->estimatedClosureAt?->format('d.m.Y');
    }

    #[Groups('project')]
    public function getRevisedDueDate(): ?string
    {
        return $this->getPhaseByNumber(4)->revisedClosureAt?->format('d.m.Y');
    }

    public function getPhaseByNumber(int $number): Phase
    {
        return $this->getPhases()->filter(static fn (Phase $phase) => $phase->number === $number)->first();
    }

    #[Groups(['project', 'project:create', 'project:edit'])]
    public function getStartedAt(): ?string
    {
        return $this->startedAt->format('d.m.Y');
    }

    #[Groups('phase')]
    public function getEstimatedHours(): int
    {
        $estimatedHours = 0;
        foreach ($this->getPhases() as $phase) {
            $estimatedHours += $phase->estimatedHours;
        }

        return $estimatedHours;
    }

    #[Groups('phase')]
    public function getRevisedEstimatedHours(): ?int
    {
        $revisedEstimatedHours = 0;
        foreach ($this->getPhases() as $phase) {
            $revisedEstimatedHours += $phase->revisedEstimatedHours;
        }

        return 0 === $revisedEstimatedHours ? null : $revisedEstimatedHours;
    }

    public function isConfidential(): bool
    {
        return $this->confidential;
    }

    public function getLegacyId(): ?int
    {
        return $this->legacyId;
    }

    public function setLegacyId(int $legacyId): self
    {
        $this->legacyId = $legacyId;

        return $this;
    }
}
