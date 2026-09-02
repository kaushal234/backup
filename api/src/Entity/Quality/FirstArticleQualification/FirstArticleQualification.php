<?php

declare(strict_types=1);

namespace App\Entity\Quality\FirstArticleQualification;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
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
use App\Controller\File\DownloadController;
use App\Controller\Quality\FirstArticleQualification\FirstArticleQualificationDeleteFileController;
use App\Controller\Quality\FirstArticleQualification\FirstArticleQualificationUploadFileController;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\Entity\Sales\ProductFamily;
use App\Entity\SupplierEntityInterface;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\Quality\FirstArticleQualificationNoPlanFilter;
use App\Filter\SimpleSearchFilter;
use App\ION\Validator\Constraints\MasterData\BusinessPartners\BusinessPartnerSupplier;
use App\Serializer\Filter\ContextFilter;
use App\Validator\Constraints\Location as ValidLocation;
use App\Validator\FirstArticleQualificationGroupsGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Filter\NoOpenTasksFilter;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\Quality\FirstArticleQualification\FirstArticleQualificationRepository')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['faq', 'faq:light', 'people_public', 'expose_legacy', 'faq_part_number', 'location_public']]),
        new Post(security: "is_granted('FEATURE_FAQ_CREATE')"),
        new Post(
            uriTemplate: '/first_article_qualifications/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: FirstArticleQualificationUploadFileController::class,
            security: "is_granted('FEATURE_FAQ_CREATE') or is_granted('FEATURE_FAQ_PLAN_WRITE')",
            deserialize: false,
            forceEager: false,
            name: 'upload_first_article_qualification_file',
        ),
        new Put(
            securityPostDenormalize: "is_granted('FEATURE_FAQ_PLAN_WRITE', object) or user in [previous_object.getOwner(), previous_object.getPoster(), previous_object.getBuyer()]",
            forceEager: false,
        ),
        new Put(
            uriTemplate: '/first_article_qualifications/{id}/status',
            denormalizationContext: ['groups' => ['faq:status']],
            security: "is_granted('FAQ_STATUS_VOTER', object)",
            validate: false,
            forceEager: false,
            name: 'update_first_article_qualification_status',
        ),
        new Get(forceEager: false),
        new Get(
            uriTemplate: '/first_article_qualifications/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: FirstArticleQualificationFile::class),
                'id' => new Link(fromClass: FirstArticleQualification::class),
            ],
            defaults: ['parentProperty' => 'firstArticleQualification', 'class' => FirstArticleQualificationFile::class],
            controller: DownloadController::class,
            forceEager: false,
            name: 'download_first_article_qualification_file',
        ),
        new Delete(
            uriTemplate: '/first_article_qualifications/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: FirstArticleQualificationFile::class),
                'id' => new Link(fromClass: FirstArticleQualification::class),
            ],
            controller: FirstArticleQualificationDeleteFileController::class,
            security: "is_granted('FAQ_DELETE_FILE_VOTER', object)",
            forceEager: false,
            name: 'delete_first_article_qualification_file',
        ),
        new Delete(security: "is_granted('FEATURE_FAQ_DELETE')"),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['faq_detail', 'faq_plan_item_detail', 'faq_part_number', 'faq_plan_item', 'people_public', 'file', 'expose_legacy', 'location_public', 'tag', 'faq_item_type_detail', 'equipment_record', 'catalogue_family_list']],
    denormalizationContext: ['groups' => ['faq_write', 'faq_part_number_write', 'tag_write']],
    validationContext: ['groups' => FirstArticleQualificationGroupsGenerator::class],
)]
#[ORM\Table(name: 'first_article_qualifications')]
#[ApiFilter(OrderFilter::class, properties: ['createdAt' => 'DESC', 'id' => 'ASC'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['status' => 'exact', 'iFactor' => 'exact', 'planApprovalStatus' => 'exact', 'poster' => 'exact', 'owner' => 'exact', 'buyer' => 'exact', 'location' => 'exact', 'location.name' => 'exact', 'supplierNumber' => 'partial', 'supplierName' => 'partial', 'partNumbers.number' => 'exact', 'partNumbers.revision' => 'exact', 'partNumbers.description' => 'partial', 'tags.name' => 'partial', 'equipmentRecords.serialNumber' => 'exact', 'productFamily' => 'exact', 'id' => 'exact'])]
#[ApiFilter(NumericFilter::class, properties: ['eap', 'meap'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact', 'completedAt' => 'exclude_null', 'planDefinitionDueDate' => 'exact', 'dueDate' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['people_photo', 'file:light', 'faq_progress']])]
#[ApiFilter(ExistsFilter::class, properties: ['planDefinitionCompletedAt', 'plan'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['faq_export']])]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(NoOpenTasksFilter::class)]
#[ApiFilter(FirstArticleQualificationNoPlanFilter::class)]
#[App\Loggable]
#[Gedmo\SoftDeleteable]
class FirstArticleQualification implements SupplierEntityInterface, UpdatableStatusEntityInterface
{
    /**
     * @var string
     */
    final public const PENDING = 'PENDING';
    /**
     * @var string
     */
    final public const IN_PROGRESS = 'IN_PROGRESS';
    /**
     * @var string
     */
    final public const IN_PROGRESS_PLAN_COMPLETED = 'IN PROGRESS / PLAN 100% COMPLETED';
    /**
     * @var string
     */
    final public const CONDITIONAL = 'CONDITIONAL';
    /**
     * @var string
     */
    final public const QUALIFIED = 'QUALIFIED';
    /**
     * @var string
     */
    final public const REJECTED = 'REJECTED';

    /**
     * @var string
     */
    final public const APPROVED = 'APPROVED';
    /**
     * @var string
     */
    final public const UNAPPROVED = 'UNAPPROVED';
    /**
     * @var string
     */
    final public const NOT_APPROVED_YET = 'NOT_APPROVED_YET';

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $deletedAt = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['faq', 'faq:light', 'faq_detail', 'faq_export'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    #[ValidLocation(factory: true)]
    private Location $location;

    /**
     * @var Collection<PartNumber>
     */
    #[ORM\OneToMany(mappedBy: 'firstArticleQualification', targetEntity: 'App\Entity\Quality\FirstArticleQualification\PartNumber', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(min: 1, minMessage: 'There should be at least one partNumber')]
    #[Assert\Valid]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    private Collection $partNumbers;

    #[BusinessPartnerSupplier]
    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotNull(groups: ['buyer'])]
    #[Assert\NotBlank(groups: ['buyer'])]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'buyer', 'faq_export'])]
    private ?string $supplierNumber = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotNull(groups: ['buyer'])]
    #[Assert\NotBlank(groups: ['buyer'])]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    private ?string $supplierName = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    #[Transferable(handler: 'handler.faq')]
    private ?People $buyer = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['faq', 'faq_detail', 'faq_export'])]
    #[Exclude]
    #[Transferable(handler: 'handler.faq')]
    #[Gedmo\Blameable(on: 'create')]
    private People $poster;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    #[Assert\NotNull]
    #[Transferable(handler: 'handler.faq')]
    private People $owner;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinTable(name: 'first_article_qualifications_members')]
    #[Groups(['faq_detail', 'faq_write'])]
    private Collection $members;

    /**
     * @var Collection<FirstArticleQualificationFile>
     */
    #[ORM\OneToMany(mappedBy: 'firstArticleQualification', targetEntity: 'App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['faq_detail'])]
    private Collection $files;

    #[ORM\Column(type: 'datetime')]
    #[Assert\Type('DateTimeInterface')]
    #[Groups(['faq', 'faq_detail', 'faq_export'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\Type('DateTimeInterface')]
    #[Groups(['faq', 'faq_detail', 'faq_export'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::QUALIFIED, self::REJECTED])]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\Type('DateTimeInterface')]
    #[Groups(['faq', 'faq_detail', 'faq_export'])]
    private ?\DateTimeInterface $planDefinitionCompletedAt = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Type('DateTimeInterface')]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    private \DateTimeInterface $planDefinitionDueDate;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    private ?\DateTimeInterface $deliverablesDueDate = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Type('DateTimeInterface')]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    private \DateTimeInterface $dueDate;

    /**
     * @var Collection<FirstArticleQualificationTag>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationTag', mappedBy: 'firstArticleQualifications')]
    #[Assert\Valid]
    #[Groups(['faq_detail', 'faq_write', 'faq_export'])]
    private Collection $tags;

    /**
     * @var Collection<PlanItem>
     */
    #[ORM\OneToMany(mappedBy: 'firstArticleQualification', targetEntity: 'App\Entity\Quality\FirstArticleQualification\PlanItem', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(['faq_detail', 'faq_plan_item_write', 'faq_fetch_eager'])]
    private Collection $plan;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    private ?int $eap = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    private ?int $meap = null;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::PENDING, self::REJECTED, self::QUALIFIED, self::CONDITIONAL, self::IN_PROGRESS, self::IN_PROGRESS_PLAN_COMPLETED])]
    #[Groups(['faq', 'faq_detail', 'faq_export', 'faq:status'])]
    private string $status = self::PENDING;

    /**
     * @var Collection<EquipmentRecord>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\EquipmentRecord')]
    #[ORM\JoinTable(name: 'first_article_qualifications_equipment_records')]
    #[Groups(['faq_detail', 'faq_write'])]
    private Collection $equipmentRecords;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::APPROVED, self::UNAPPROVED, self::NOT_APPROVED_YET])]
    #[Groups(['faq', 'faq_detail', 'faq_plan_approval_write', 'faq_export'])]
    private string $planApprovalStatus = self::NOT_APPROVED_YET;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    #[Groups(['faq', 'faq_detail', 'faq_write', 'faq_export'])]
    #[Assert\Type('string')]
    #[Assert\Choice(choices: ['IF1', 'IF10', 'IF100', 'IF1000'])]
    private ?string $iFactor = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ProductFamily')]
    #[Groups(['faq_detail', 'faq_write', 'faq_export'])]
    private ?ProductFamily $productFamily = null;

    /**
     * @var Collection<Crab>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Quality\Crab', mappedBy: 'firstArticleQualification')]
    #[Groups(['faq_detail'])]
    private Collection $crabs;

    public function __construct()
    {
        $this->partNumbers = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->plan = new ArrayCollection();
        $this->crabs = new ArrayCollection();
        $this->equipmentRecords = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<PartNumber>
     */
    public function getPartNumbers(): Collection
    {
        return $this->partNumbers;
    }

    public function addPartNumber(PartNumber $partNumber): self
    {
        $partNumber->setFirstArticleQualification($this);
        $this->partNumbers->add($partNumber);

        return $this;
    }

    public function removePartNumber(PartNumber $partNumber): self
    {
        $this->partNumbers->removeElement($partNumber);

        return $this;
    }

    public function getSupplierNumber(): ?string
    {
        return $this->supplierNumber;
    }

    public function setSupplierNumber(?string $supplierNumber): self
    {
        $this->supplierNumber = $supplierNumber;

        return $this;
    }

    public function getSupplierName(): ?string
    {
        return $this->supplierName;
    }

    public function setSupplierName(?string $supplierName): self
    {
        $this->supplierName = $supplierName;

        return $this;
    }

    public function getBuyer(): ?People
    {
        return $this->buyer;
    }

    public function setBuyer(?People $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getPoster(): People
    {
        return $this->poster;
    }

    public function setPoster(People $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getOwner(): People
    {
        return $this->owner;
    }

    public function setOwner(People $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<People>
     */
    public function getMembers()
    {
        return $this->members;
    }

    public function addMember(People $member): self
    {
        $this->members->add($member);

        return $this;
    }

    /**
     * @param Collection<People> $members
     */
    public function setMembers(Collection $members): self
    {
        $this->members = $members;

        return $this;
    }

    public function removeMember(People $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }

    /**
     * @return Collection<FirstArticleQualificationFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(FirstArticleQualificationFile $file): self
    {
        $this->files->add($file);
        $file->setFirstArticleQualification($this);

        return $this;
    }

    public function removeFile(FirstArticleQualificationFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getPlanDefinitionCompletedAt(): ?\DateTimeInterface
    {
        return $this->planDefinitionCompletedAt;
    }

    public function setPlanDefinitionCompletedAt(?\DateTimeInterface $planDefinitionCompletedAt): self
    {
        $this->planDefinitionCompletedAt = $planDefinitionCompletedAt;

        return $this;
    }

    public function getPlanDefinitionDueDate(): \DateTimeInterface
    {
        return $this->planDefinitionDueDate;
    }

    public function setPlanDefinitionDueDate(\DateTimeInterface $planDefinitionDueDate): self
    {
        $this->planDefinitionDueDate = $planDefinitionDueDate;

        return $this;
    }

    public function getDueDate(): \DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * @return Collection<FirstArticleQualificationTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(FirstArticleQualificationTag $tag): self
    {
        $tag->addFirstArticleQualification($this);
        $this->tags->add($tag);

        return $this;
    }

    public function removeTag(FirstArticleQualificationTag $tag): self
    {
        $tag->removeFirstArticleQualification($this);
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<PlanItem>
     */
    public function getPlan(): Collection
    {
        return $this->plan;
    }

    public function setPlan(ArrayCollection $planItems): self
    {
        $this->plan = $planItems;

        return $this;
    }

    public function addPlan(PlanItem $plan): self
    {
        $plan->setFirstArticleQualification($this);
        $this->plan->add($plan);

        return $this;
    }

    public function removePlan(PlanItem $plan): self
    {
        $this->plan->removeElement($plan);

        return $this;
    }

    public function getEap(): ?int
    {
        return $this->eap;
    }

    public function setEap(?int $eap): self
    {
        $this->eap = $eap;

        return $this;
    }

    public function getMeap(): ?int
    {
        return $this->meap;
    }

    public function setMeap(?int $meap): self
    {
        $this->meap = $meap;

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

    /**
     * @return Collection<EquipmentRecord>
     */
    public function getEquipmentRecords()
    {
        return $this->equipmentRecords;
    }

    /**
     * @param Collection<EquipmentRecord> $equipmentRecords
     */
    public function setEquipmentRecords($equipmentRecords): self
    {
        $this->equipmentRecords = $equipmentRecords;

        return $this;
    }

    public function addEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecords->add($equipmentRecord);

        return $this;
    }

    public function removeEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecords->removeElement($equipmentRecord);

        return $this;
    }

    public function getPlanApprovalStatus(): string
    {
        return $this->planApprovalStatus;
    }

    public function setPlanApprovalStatus(string $planApprovalStatus): self
    {
        $this->planApprovalStatus = $planApprovalStatus;

        return $this;
    }

    public function getIFactor(): ?string
    {
        return $this->iFactor;
    }

    public function setIFactor(?string $iFactor): self
    {
        $this->iFactor = $iFactor;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getProductFamily(): ?ProductFamily
    {
        return $this->productFamily;
    }

    public function setProductFamily(?ProductFamily $productFamily): self
    {
        $this->productFamily = $productFamily;

        return $this;
    }

    /**
     * @return Collection<Crab>
     */
    public function getCrabs(): Collection
    {
        return $this->crabs;
    }

    public function addCrab(Crab $crab): self
    {
        if (!$this->crabs->contains($crab)) {
            $this->crabs->add($crab);
            $crab->firstArticleQualification = $this;
        }

        return $this;
    }

    public function removeCrab(Crab $crab): self
    {
        if ($this->crabs->contains($crab)) {
            $this->crabs->removeElement($crab);
        }

        return $this;
    }

    public function getDeliverablesDueDate(): ?\DateTimeInterface
    {
        return $this->deliverablesDueDate;
    }

    public function setDeliverablesDueDate(?\DateTimeInterface $deliverablesDueDate): self
    {
        $this->deliverablesDueDate = $deliverablesDueDate;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->plan->isEmpty() && null !== $this->planDefinitionCompletedAt) {
            $context
                ->buildViolation('You cannot reset a plan')
                ->atPath('plan')
                ->addViolation();
        }

        if ($this->dueDate < $this->planDefinitionDueDate) {
            $context
                ->buildViolation('The due date must be superior to the plan definition due date.')
                ->atPath('dueDate')
                ->addViolation();
        }

        if (self::NOT_APPROVED_YET !== $this->planApprovalStatus && $this->plan->isEmpty()) {
            $context
                ->buildViolation('The plan must be defined to be APPROVED or UNAPPROVED')
                ->atPath('planApprovalStatus')
                ->addViolation();
        }
    }

    public function getBusinessPartnerCode(): ?string
    {
        return $this->getSupplierName();
    }
}
