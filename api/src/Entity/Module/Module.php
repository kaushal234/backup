<?php

declare(strict_types=1);

namespace App\Entity\Module;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\FreeTextQueryFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\MIS\ThirdPartyApp\ConvertToExtendedController;
use App\Controller\MIS\ThirdPartyApp\ConvertToLightController;
use App\Controller\ModulesTreeController;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\Application;
use App\Entity\MIS\TroubleTicket\TypeDefaultAssignee;
use App\Entity\Module\Specification\Specification;
use App\Entity\Module\ThirdPartyApp\Type as ThirdPartyApp;
use App\Filter\ColumnsFilter;
use App\Filter\DiscriminatorFilter;
use App\Filter\MIS\Module\ModuleWithOpenUpdateTasksOnlyFilter;
use App\Filter\MIS\Module\OrderByOpenUpdateTasksCountFilter;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\MIS\Module\ModuleNotification;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Module.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\Module\ModuleRepository')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ModuleNotification]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    self::TYPE_MODULE => Module::class,
    self::TYPE_THIRD_PARTY_APP_LIGHT => ThirdPartyApp\Light::class,
    self::TYPE_THIRD_PARTY_APP_EXTENDED => ThirdPartyApp\Extended::class,
    self::TYPE_THIRD_PARTY_APP_CONNECTED => ThirdPartyApp\Connected::class,
])]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['module', 'people_public', 'people_list', 'people:business_unit', 'position', 'people_photo', 'file:light', 'expose_legacy', 'department_list', 'type_assignee', 'third_party_app', 'business_unit', 'security_level', 'application', 'count_update_tasks']],
            parameters: [
                'exact[name]' => new QueryParameter(
                    filter: new FreeTextQueryFilter(new ExactFilter()),
                    description: 'To allow filtering by exact name.',
                    properties: ['name'],
                ),
            ],
        ),
        new GetCollection(
            uriTemplate: '/modules_tree',
            controller: ModulesTreeController::class,
            normalizationContext: ['groups' => ['expose_legacy', 'module_tree']],
            read: false,
            name: 'tree',
        ),
        new Post(
            security: "is_granted('FEATURE_MODULE_WRITE')",
            validationContext: ['groups' => ['module_create']]
        ),
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['module_write', 'module_status']],
            security: "is_granted('FEATURE_MODULE_WRITE')"
        ),
        new Put(
            uriTemplate: '/modules/{id}/convert/third_party_app_light',
            controller: ConvertToLightController::class,
            normalizationContext: ['groups' => ['module', 'module_detail']],
            denormalizationContext: ['groups' => ['module_write', 'module_status']],
            security: "is_granted('FEATURE_MODULE_WRITE')",
        ),
        new Put(
            uriTemplate: '/modules/{id}/convert/third_party_app_extended',
            controller: ConvertToExtendedController::class,
            normalizationContext: ['groups' => ['module', 'module_detail']],
            denormalizationContext: ['groups' => ['module_write', 'module_status']],
            security: "is_granted('FEATURE_MODULE_WRITE')",
        ),
    ],
    normalizationContext: ['groups' => ['module', 'module_detail', 'people_public', 'people_photo', 'file:light', 'expose_legacy', 'department_list', 'type_assignee', 'application', 'region:list', 'security_level', 'business_unit']],
    denormalizationContext: ['groups' => ['module_write']],
    order: ['name' => 'ASC'],
)]
#[ORM\Table(name: 'modules')]
#[ApiFilter(DiscriminatorFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['name', 'shortDescription', 'application.name', 'department.name', 'disabledForTroubleTicket'])]
#[ApiFilter(BooleanFilter::class, properties: ['disabledForTroubleTicket'])]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'name' => 'partial',
    'shortDescription' => 'partial',
    'legacyId',
    'application',
    'application.name' => 'partial',
    'department.name' => 'partial',
    'operationalOwner',
    'status',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'name' => 'partial',
    'shortDescription' => 'partial',
    'application.name' => 'partial',
    'department.name' => 'partial',
    'operationalOwner.firstname' => 'partial',
    'operationalOwner.lastname' => 'partial',
    'keyUser.firstname' => 'partial',
    'keyUser.lastname' => 'partial',
    'status' => 'exact',
])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'com_modules')]
#[Legacy\ExtraColumn(column: 'parent_id', value: 0)]
#[Legacy\ExtraColumn(column: 'link', value: '')]
#[ApiFilter(ModuleWithOpenUpdateTasksOnlyFilter::class)]
#[ApiFilter(OrderByOpenUpdateTasksCountFilter::class)]
class Module implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    public const string TYPE_MODULE = 'module';
    public const string TYPE_THIRD_PARTY_APP_LIGHT = 'third_party_app_light';
    public const string TYPE_THIRD_PARTY_APP_EXTENDED = 'third_party_app_extended';
    public const string TYPE_THIRD_PARTY_APP_CONNECTED = 'third_party_app_connected';
    public const string ACTIVE = 'ACTIVE';
    public const string DISABLED = 'DISABLED';

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['module_write', 'module_light', 'module_detail'])]
    public ?string $frontEndRoute = null;

    #[Groups(['module_write'])]
    public bool $transferOperationalOwner = false;

    #[Groups(['module_write'])]
    public bool $transferMisOwner = false;

    #[ORM\OneToOne(targetEntity: Specification::class, mappedBy: 'module', cascade: ['persist', 'remove'])]
    #[Groups(['module'])]
    #[MaxDepth(1)]
    public ?Specification $specification = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['module', 'module_status'])]
    #[Assert\Choice(choices: [self::ACTIVE, self::DISABLED])]
    public string $status = self::ACTIVE;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['module', 'module_light'])]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 20, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Groups(['module', 'module_write', 'module_public', 'module_light', 'module_tree', 'module:list'])]
    #[Legacy\Column(column: 'module', transformer: Utf8ToHtmlEntities::class)]
    #[Legacy\Copy(table: 'agr', columns: ['acronym'])]
    #[Legacy\Copy(table: 'mod_logs', columns: ['module'])]
    #[Legacy\Copy(table: 'tasks', columns: ['module'])]
    private string $name;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'operational_owner_id')]
    #[Groups(['module', 'module_write', 'module_light', 'people_public'])]
    #[Transferable]
    #[Legacy\Column(column: 'oid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?People $operationalOwner = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'mis_owner_id')]
    #[Transferable]
    #[Legacy\Column(column: 'uid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?People $misOwner = null;

    #[ORM\Column(name: 'short_description', type: 'string', length: 255)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['module', 'module_write', 'module_light', 'module_tree', 'module:list'])]
    #[Legacy\Column(column: 'dsc', transformer: Utf8ToHtmlEntities::class)]
    private string $shortDescription;

    #[ORM\Column(name: 'full_description', type: 'text', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[Groups(['module', 'module_write'])]
    #[Legacy\Column(column: 'note', transformer: Utf8ToHtmlEntities::class)]
    private ?string $fullDescription = null;

    #[ORM\Column(name: 'dms_procedure_id', type: 'integer', nullable: true)]
    #[Assert\Type(type: 'integer')]
    #[Groups(['module', 'module_write'])]
    #[Legacy\Column(column: 'user_guide_id')]
    private ?int $dmsProcedureId = null;

    #[ORM\Column(name: 'dms_help_id', type: 'integer', nullable: true)]
    #[Assert\Type(type: 'integer')]
    #[Groups(['module', 'module_write'])]
    #[Legacy\Column(column: 'help_page_id')]
    private ?int $dmsHelpId = null;

    #[ORM\Column(name: 'legacy_loc', type: 'integer')]
    #[Assert\Type(type: 'integer')]
    #[Groups(['module', 'module_write'])]
    #[Exclude]
    private int $legacyLoc = 0;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Module\ModuleMigrationStep')]
    #[ORM\JoinColumn(name: 'migration_current_step_id', nullable: true)]
    #[Groups(['module', 'module_write'])]
    private ?ModuleMigrationStep $migrationCurrentStep = null;

    #[ORM\Column(name: 'migration_estimated_hours', type: 'integer')]
    #[Assert\Type(type: 'integer')]
    #[Groups(['module', 'module_write'])]
    #[Exclude]
    private int $migrationEstimatedHours = 0;

    #[ORM\Column(name: 'migrated', type: 'boolean')]
    #[Groups(['module', 'module_write', 'module_light', 'module_tree'])]
    #[Legacy\Column(column: 'migrated')]
    private bool $migrated = false;

    #[ORM\Column(name: 'disabledForTroubleTicket', type: 'boolean')]
    #[Groups(['module', 'module_light', 'module_tree', 'module_write'])]
    private bool $disabledForTroubleTicket = false;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Department')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['module', 'module_write'])]
    private ?Department $department = null;

    /**
     * @var Collection<Module>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Module\Module', mappedBy: 'requiredModules')]
    #[Groups(['module_detail', 'module_write', 'module_tree'])]
    #[Exclude]
    private Collection $requiringModules;

    /**
     * @var Collection<Module>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Module\Module', inversedBy: 'requiringModules')]
    #[ORM\JoinTable(name: 'module_dependencies')]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'module_required_id', referencedColumnName: 'id')]
    #[MaxDepth(1)]
    #[Groups(['module_detail', 'module_write'])]
    #[Exclude]
    private Collection $requiredModules;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['module_detail', 'module_write'])]
    #[Gedmo\Timestampable(on: 'update', field: 'migrated')]
    private ?\DateTimeInterface $migratedAt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Groups(['module', 'module:list', 'module_write'])]
    private ?string $notificationColor = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['module', 'module_write', 'module_light', 'people_public'])]
    #[Transferable]
    #[Legacy\Column(column: 'key_user_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?People $keyUser = null;

    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'modules')]
    #[Groups(['module', 'module_write'])]
    private ?Application $application = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['module', 'module_write'])]
    private bool $misRelative = true;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    #[Groups(['module', 'module_write'])]
    private bool $notifyOperationalOwner = true;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    #[Groups(['module', 'module_write'])]
    private bool $notifyKeyUser = true;

    /** @var Collection<TypeDefaultAssignee> */
    #[ORM\OneToMany(mappedBy: 'module', targetEntity: TypeDefaultAssignee::class, cascade: ['all'], orphanRemoval: true)]
    #[Groups(['module', 'module_write'])]
    private Collection $typeDefaultAssignees;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: People::class)]
    #[Groups(['module_detail', 'module_write', 'module:local_key_users'])]
    private Collection $localKeyUsers;

    /**
     * Module constructor.
     */
    public function __construct()
    {
        $this->requiringModules = new ArrayCollection();
        $this->requiredModules = new ArrayCollection();
        $this->typeDefaultAssignees = new ArrayCollection();
        $this->localKeyUsers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isNotifyKeyUser(): bool
    {
        return $this->notifyKeyUser;
    }

    public function setNotifyKeyUser(bool $notifyKeyUser): self
    {
        $this->notifyKeyUser = $notifyKeyUser;

        return $this;
    }

    public function isNotifyOperationalOwner(): bool
    {
        return $this->notifyOperationalOwner;
    }

    public function setNotifyOperationalOwner(bool $notifyOperationalOwner): self
    {
        $this->notifyOperationalOwner = $notifyOperationalOwner;

        return $this;
    }

    public function getOperationalOwner(): ?People
    {
        return $this->operationalOwner;
    }

    public function setOperationalOwner(?People $operationalOwner): self
    {
        $this->operationalOwner = $operationalOwner;

        return $this;
    }

    public function getMisOwner(): ?People
    {
        return $this->misOwner;
    }

    /**
     * @return $this
     */
    public function setMisOwner(People $misOwner): self
    {
        $this->misOwner = $misOwner;

        return $this;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getFullDescription(): ?string
    {
        return $this->fullDescription;
    }

    public function setFullDescription(?string $fullDescription): self
    {
        $this->fullDescription = $fullDescription;

        return $this;
    }

    public function getDmsProcedureId(): ?int
    {
        return $this->dmsProcedureId;
    }

    public function setDmsProcedureId(?int $dmsProcedureId): self
    {
        $this->dmsProcedureId = $dmsProcedureId;

        return $this;
    }

    public function getDmsHelpId(): ?int
    {
        return $this->dmsHelpId;
    }

    public function setDmsHelpId(?int $dmsHelpId): self
    {
        $this->dmsHelpId = $dmsHelpId;

        return $this;
    }

    public function getLegacyLoc(): int
    {
        return $this->legacyLoc;
    }

    public function setLegacyLoc(int $legacyLoc): self
    {
        $this->legacyLoc = $legacyLoc;

        return $this;
    }

    public function getMigrationCurrentStep(): ?ModuleMigrationStep
    {
        return $this->migrationCurrentStep;
    }

    public function setMigrationCurrentStep(?ModuleMigrationStep $migrationCurrentStep = null): self
    {
        $this->migrationCurrentStep = $migrationCurrentStep;

        return $this;
    }

    public function getMigrationEstimatedHours(): int
    {
        return $this->migrationEstimatedHours;
    }

    public function setMigrationEstimatedHours(int $migrationEstimatedHours): self
    {
        $this->migrationEstimatedHours = $migrationEstimatedHours;

        return $this;
    }

    public function isMigrated(): bool
    {
        return $this->migrated;
    }

    public function setMigrated(bool $migrated): self
    {
        $this->migrated = $migrated;

        return $this;
    }

    public function isDisabledForTroubleTicket(): bool
    {
        return $this->disabledForTroubleTicket;
    }

    public function setDisabledForTroubleTicket(bool $disabledForTroubleTicket): self
    {
        $this->disabledForTroubleTicket = $disabledForTroubleTicket;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department = null): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection<Module>
     */
    public function getRequiringModules(): Collection
    {
        return $this->requiringModules;
    }

    public function addRequiringModule(self $module): self
    {
        $this->requiringModules->add($module);

        return $this;
    }

    public function removeRequiringModule(self $module): self
    {
        $this->requiringModules->removeElement($module);

        return $this;
    }

    /**
     * @return Collection<Module>
     */
    public function getRequiredModules(): Collection
    {
        return $this->requiredModules;
    }

    public function addRequiredModule(self $module): self
    {
        $this->requiredModules->add($module);
        $module->addRequiringModule($this);

        return $this;
    }

    public function removeRequiredModule(self $module): self
    {
        $this->requiredModules->removeElement($module);
        $module->removeRequiringModule($this);

        return $this;
    }

    public function getMigratedAt(): ?\DateTimeInterface
    {
        return $this->migratedAt;
    }

    public function setMigratedAt(?\DateTimeInterface $migratedAt): self
    {
        $this->migratedAt = $migratedAt;

        return $this;
    }

    public function getKeyUser(): ?People
    {
        return $this->keyUser;
    }

    public function setKeyUser(?People $keyUser): self
    {
        $this->keyUser = $keyUser;

        return $this;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return Collection<TypeDefaultAssignee>
     */
    public function getTypeDefaultAssignees(): Collection
    {
        return $this->typeDefaultAssignees;
    }

    public function setTypeDefaultAssignees(Collection $typeDefaultAssignees): self
    {
        $this->typeDefaultAssignees = $typeDefaultAssignees;

        return $this;
    }

    public function addTypeDefaultAssignee(TypeDefaultAssignee $typeDefaultAssignee): self
    {
        if (!$this->typeDefaultAssignees->contains($typeDefaultAssignee)) {
            $this->typeDefaultAssignees->add($typeDefaultAssignee);
            $typeDefaultAssignee->module = $this;
        }

        return $this;
    }

    public function removeTypeDefaultAssignee(TypeDefaultAssignee $typeDefaultAssignee): self
    {
        if (!$this->typeDefaultAssignees->contains($typeDefaultAssignee)) {
            $this->typeDefaultAssignees->removeElement($typeDefaultAssignee);
        }

        return $this;
    }

    /**
     * @return Collection<People>
     */
    public function getLocalKeyUsers(): Collection
    {
        return $this->localKeyUsers;
    }

    public function setLocalKeyUsers(Collection $localKeyUsers): self
    {
        $this->localKeyUsers = $localKeyUsers;

        return $this;
    }

    public function addLocalKeyUser(People $localKeyUser): self
    {
        if (!$this->localKeyUsers->contains($localKeyUser)) {
            $this->localKeyUsers->add($localKeyUser);
        }

        return $this;
    }

    public function removeLocalKeyUser(People $localKeyUser): self
    {
        if ($this->localKeyUsers->contains($localKeyUser)) {
            $this->localKeyUsers->removeElement($localKeyUser);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        $regionLocalKeyUserAlreadySet = [];
        foreach ($this->getLocalKeyUsers() as $localKeyUser) {
            $regionName = $localKeyUser->getBusinessUnit()?->getRegion()?->getName();
            if (null === $regionName) {
                continue;
            }

            if (\in_array($regionName, $regionLocalKeyUserAlreadySet, true)) {
                $context->buildViolation('Only one LKU per Region is permitted.')->atPath('localKeyUsers')->addViolation();
            }

            $regionLocalKeyUserAlreadySet[] = $regionName;
        }
    }

    public function isMisRelative(): bool
    {
        return $this->misRelative;
    }

    public function setMisRelative(bool $misRelative): self
    {
        $this->misRelative = $misRelative;

        return $this;
    }

    public function getNotificationColor(): ?string
    {
        return $this->notificationColor;
    }

    public function setNotificationColor(?string $notificationColor): self
    {
        $this->notificationColor = $notificationColor;

        return $this;
    }
}
