<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
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
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EmissionRating;
use App\Entity\EquipmentRecord;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\Demo\DemoOpenFilter;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\Sales\DemoRepository')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['demo', 'people_public', 'country_list', 'expose_legacy', 'product_list', 'customer_list', 'location_public', 'equipment_list', 'airport_list']]),
        new Put(security: "is_granted('DEMO_EDIT_VOTER', object)"),
        new Put(
            uriTemplate: '/demos/{id}/status',
            denormalizationContext: ['groups' => ['demo:update_status']],
            security: "is_granted('DEMO_STATUS_VOTER', object)",
            name: 'update_demo_status',
        ),
        new Delete(
            uriTemplate: '/demos/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'demoFiles', fromClass: DemoFile::class),
                'id' => new Link(fromClass: Demo::class),
            ],
            defaults: ['parentProperty' => 'demo', 'class' => DemoFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_DEMO_ADMIN', object) or is_granted('MOO_DEMO')",
            name: 'delete_demo_file',
        ),
        new Post(
            denormalizationContext: ['groups' => ['demo_write', 'demo_create']],
            security: "is_granted('FEATURE_DEMO_CREATE') or is_granted('MOO_DEMO')",
        ),
        new Post(
            uriTemplate: '/demos/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getDemoFiles', 'class' => DemoFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_DEMO_EDIT') or is_granted('MOO_DEMO') or is_granted('FEATURE_DEMO_ADMIN')",
            deserialize: false,
            name: 'upload_demo_file',
        ),
        new Delete(security: "is_granted('FEATURE_DEMO_DELETE')"),
        new Get(),
        new Get(
            uriTemplate: '/demos/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'demoFiles', fromClass: DemoFile::class),
                'id' => new Link(fromClass: Demo::class),
            ],
            defaults: ['parentProperty' => 'demo', 'class' => DemoFile::class],
            controller: DownloadController::class,
            name: 'download_demo_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['demo', 'demo_detail', 'people_list', 'file', 'country_list', 'expose_legacy', 'location_public', 'product_restricted', 'customer_list', 'iata_code_restricted', 'equipment_list']],
    denormalizationContext: ['groups' => ['demo_write']],
)]
#[ORM\Table(name: 'demos')]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'expectedStartDate' => 'ASC'])]
#[ApiFilter(BooleanFilter::class, properties: ['delinquent'])]
#[ApiFilter(DateFilter::class, properties: ['expectedStartDate'])]
#[ApiFilter(DemoOpenFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['asm' => 'exact', 'customer' => 'exact', 'customer.type' => 'exact', 'customer.customerTypes' => 'exact', 'product' => 'exact', 'factory' => 'exact', 'sso' => 'exact', 'country' => 'exact', 'equipmentRecord' => 'exact', 'equipmentRecord.legacyId' => 'exact', 'psm' => 'exact', 'status' => 'exact'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['demo_list']])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['workflow']])]
#[App\Loggable]
class Demo implements UpdatableStatusEntityInterface
{
    /**
     * @var string
     */
    final public const SUBMITTED = 'SUBMITTED';
    /**
     * @var string
     */
    final public const PENDING = 'PENDING';
    /**
     * @var string
     */
    final public const APPROVED = 'APPROVED';
    /**
     * @var string
     */
    final public const ACTIVE = 'ACTIVE';
    /**
     * @var string
     */
    final public const REJECTED = 'REJECTED';
    /**
     * @var string
     */
    final public const SUCCESSFUL = 'SUCCESSFUL';
    /**
     * @var string
     */
    final public const SUCCESSFUL_FUTURE_SALE = 'SUCCESSFUL_FUTURE_SALE';
    /**
     * @var string
     */
    final public const UNSUCCESSFUL = 'UNSUCCESSFUL';
    /**
     * @var string
     */
    final public const CANCELLED = 'CANCELLED';

    final public const OPEN_STATUSES = [self::SUBMITTED, self::PENDING, self::APPROVED, self::ACTIVE];
    final public const CLOSED_STATUSES = [self::REJECTED, self::SUCCESSFUL, self::SUCCESSFUL_FUTURE_SALE, self::UNSUCCESSFUL, self::CANCELLED];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['demo', 'demo_detail', 'demo_list'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    #[ValidLocation(sso: true)]
    private Location $sso;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    #[ValidLocation(factory: true)]
    private Location $factory;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    #[Transferable(handler: 'handler.demo.representatives', manager: 'manager.customer.representative')]
    #[Gedmo\Blameable(on: 'create')]
    private People $asm;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['demo_detail', 'demo_write'])]
    #[Transferable(handler: 'handler.demo.representatives')]
    private People $psm;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull]
    #[Groups(['demo_detail', 'demo_write_admin', 'demo:update_ast'])]
    #[Transferable(handler: 'handler.demo.representatives')]
    private ?People $ast = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['demo', 'demo_detail', 'demo_write', 'demo_list'])]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['demo', 'demo_detail', 'demo_write', 'demo_list'])]
    private Customer $customer;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Country')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    private Country $country;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Common\Airport')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    private ?Airport $airport = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    private ?\DateTimeInterface $expectedStartDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['demo', 'demo_detail'])]
    private ?\DateTimeInterface $actualStartDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['demo', 'demo_detail', 'demo_create'])]
    private ?\DateTimeInterface $expectedEndDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    private ?\DateTimeInterface $revisedEndDate = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    private ?\DateTimeInterface $closingDate = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EquipmentRecord', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    private ?EquipmentRecord $equipmentRecord = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['demo_detail', 'demo_write'])]
    private ?string $closingComment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['demo', 'demo_detail', 'demo_write'])]
    private ?string $comment = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotNull]
    #[Groups(['demo', 'demo_detail', 'demo:update_status'])]
    private string $status = self::PENDING;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['demo', 'demo_detail'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status')]
    private ?\DateTimeInterface $statusUpdatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: 'ACTIVE')]
    #[Groups(['demo_detail'])]
    private ?\DateTimeInterface $activatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['demo', 'demo_detail'])]
    #[Gedmo\Timestampable(on: 'change', field: 'comment')]
    private ?\DateTimeInterface $lastCommentedAt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::SUCCESSFUL, self::SUCCESSFUL_FUTURE_SALE, self::UNSUCCESSFUL])]
    #[Groups(['demo_detail', 'demo_write'])]
    private ?string $expectedClosingStatus = null;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Sales\Demo')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[MaxDepth(1)]
    #[Groups(['demo_detail', 'demo_write'])]
    private ?Demo $futureDemo = null;

    /**
     * @var Collection<DemoFile>
     */
    #[ORM\OneToMany(mappedBy: 'demo', targetEntity: 'App\Entity\Sales\DemoFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['demo_detail'])]
    private Collection $demoFiles;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['demo', 'demo_detail'])]
    private bool $delinquent = false;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['demo_detail', 'demo_write'])]
    private Collection $approvers;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['demo_detail'])]
    private ?int $sequenceId = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EmissionRating')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['demo_detail', 'demo_write'])]
    private ?EmissionRating $emissionRating = null;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['demo_detail', 'demo_write'])]
    private bool $linkAllocated = true;

    public function __construct()
    {
        $this->demoFiles = new ArrayCollection();
        $this->approvers = new ArrayCollection();
        $this->lastCommentedAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSso(): Location
    {
        return $this->sso;
    }

    public function setSso(Location $sso): self
    {
        $this->sso = $sso;

        return $this;
    }

    public function getFactory(): Location
    {
        return $this->factory;
    }

    public function setFactory(Location $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function getAsm(): People
    {
        return $this->asm;
    }

    public function setAsm(People $asm): self
    {
        $this->asm = $asm;

        return $this;
    }

    public function getPsm(): People
    {
        return $this->psm;
    }

    public function setPsm(People $psm): self
    {
        $this->psm = $psm;

        return $this;
    }

    public function getAst(): ?People
    {
        return $this->ast;
    }

    public function setAst(?People $ast): self
    {
        $this->ast = $ast;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getAirport(): ?Airport
    {
        return $this->airport;
    }

    public function setAirport(?Airport $airport): self
    {
        $this->airport = $airport;

        return $this;
    }

    public function getExpectedStartDate(): ?\DateTimeInterface
    {
        return $this->expectedStartDate;
    }

    public function setExpectedStartDate(?\DateTimeInterface $expectedStartDate): self
    {
        $this->expectedStartDate = $expectedStartDate;

        return $this;
    }

    public function getExpectedEndDate(): ?\DateTimeInterface
    {
        return $this->expectedEndDate;
    }

    public function setExpectedEndDate(?\DateTimeInterface $expectedEndDate): self
    {
        $this->expectedEndDate = $expectedEndDate;

        return $this;
    }

    public function getRevisedEndDate(): ?\DateTimeInterface
    {
        return $this->revisedEndDate;
    }

    public function setRevisedEndDate(?\DateTimeInterface $revisedEndDate): self
    {
        $this->revisedEndDate = $revisedEndDate;

        return $this;
    }

    public function getClosingDate(): ?\DateTimeInterface
    {
        return $this->closingDate;
    }

    public function setClosingDate(?\DateTimeInterface $closingDate): self
    {
        $this->closingDate = $closingDate;

        return $this;
    }

    public function getEquipmentRecord(): ?EquipmentRecord
    {
        return $this->equipmentRecord;
    }

    public function setEquipmentRecord(?EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecord = $equipmentRecord;

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusUpdatedAt(): ?\DateTimeInterface
    {
        return $this->statusUpdatedAt;
    }

    public function setStatusUpdatedAt(?\DateTimeInterface $statusUpdatedAt): self
    {
        $this->statusUpdatedAt = $statusUpdatedAt;

        return $this;
    }

    public function getFutureDemo(): ?self
    {
        return $this->futureDemo;
    }

    public function setFutureDemo(?self $futureDemo): self
    {
        $this->futureDemo = $futureDemo;

        return $this;
    }

    /**
     * @return Collection<DemoFile>
     */
    public function getDemoFiles(): Collection
    {
        return $this->demoFiles;
    }

    public function addDemoFile(DemoFile $demoFile): self
    {
        $demoFile->setDemo($this);
        $this->demoFiles->add($demoFile);

        return $this;
    }

    public function removeDemoFile(DemoFile $demoFile): self
    {
        $this->demoFiles->removeElement($demoFile);

        return $this;
    }

    public function isDelinquent(): bool
    {
        return $this->delinquent;
    }

    public function setDelinquent(bool $delinquent): self
    {
        $this->delinquent = $delinquent;

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

    public function getSequenceId(): ?int
    {
        return $this->sequenceId;
    }

    public function setSequenceId(?int $sequenceId): self
    {
        $this->sequenceId = $sequenceId;

        return $this;
    }

    public function getExpectedClosingStatus(): ?string
    {
        return $this->expectedClosingStatus;
    }

    public function setExpectedClosingStatus(?string $expectedClosingStatus): self
    {
        $this->expectedClosingStatus = $expectedClosingStatus;

        return $this;
    }

    public function getApprovers(): Collection
    {
        return $this->approvers;
    }

    public function addApprover(People $people): self
    {
        $this->approvers->add($people);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeApprover(People $people): self
    {
        $this->approvers->removeElement($people);

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

    public function getActualStartDate(): ?\DateTimeInterface
    {
        return $this->actualStartDate;
    }

    public function setActualStartDate(?\DateTimeInterface $actualStartDate): self
    {
        $this->actualStartDate = $actualStartDate;

        return $this;
    }

    public function getActivatedAt(): ?\DateTimeInterface
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?\DateTimeInterface $activatedAt): self
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    public function getLastCommentedAt(): ?\DateTimeInterface
    {
        return $this->lastCommentedAt;
    }

    public function setLastCommentedAt(?\DateTimeInterface $lastCommentedAt): self
    {
        $this->lastCommentedAt = $lastCommentedAt;

        return $this;
    }

    public function getEmissionRating(): ?EmissionRating
    {
        return $this->emissionRating;
    }

    public function setEmissionRating(?EmissionRating $emissionRating): self
    {
        $this->emissionRating = $emissionRating;

        return $this;
    }

    public function getLinkAllocated(): bool
    {
        return $this->linkAllocated;
    }

    public function setLinkAllocated(bool $linkAllocated): void
    {
        $this->linkAllocated = $linkAllocated;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null !== $this->expectedEndDate && null === $this->revisedEndDate && $this->expectedEndDate < $this->expectedStartDate) {
            $context
                ->buildViolation(\sprintf('The expected end date should be greater than %s.', $this->expectedStartDate->format('Y-m-d')))
                ->atPath('endDate')
                ->addViolation()
            ;
        }
        if (null !== $this->revisedEndDate && $this->revisedEndDate < $this->expectedStartDate) {
            $context
                ->buildViolation(\sprintf('The revised end date should be greater than %s.', $this->expectedStartDate->format('Y-m-d')))
                ->atPath('revisedEndDate')
                ->addViolation()
            ;
        }
        if (null !== $this->revisedEndDate && $this->revisedEndDate < $this->actualStartDate) {
            $context
                ->buildViolation(\sprintf('The revised end date should be greater than %s.', $this->actualStartDate->format('Y-m-d')))
                ->atPath('revisedEndDate')
                ->addViolation()
            ;
        }
    }
}
