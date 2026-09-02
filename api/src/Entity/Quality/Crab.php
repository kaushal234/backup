<?php

declare(strict_types=1);

namespace App\Entity\Quality;

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
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Parts\CrabPart;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use App\Repository\Quality\Crab\CrabRepository;
use App\Validator\Constraints\CrabAssyCategory;
use App\Validator\Constraints\CrabCodeRequirePartNumber;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Timestampable;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[CrabCodeRequirePartNumber]
#[CrabAssyCategory]
#[ORM\Entity(repositoryClass: CrabRepository::class)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'equipmentRecord.serialNumber',
    'equipmentRecord.manufacturerLocation.name',
    'equipmentRecord.product.family.productType.englishName',
    'category',
    'department.name',
    'code.code',
    'createdBy.lastname',
    'createdAt',
    'fixedBy.lastname',
    'fixedAt',
    'inspectedBy.lastname',
    'inspectedAt',
])]
#[ApiFilter(SearchFilter::class, properties: [
    'id',
    'createdBy',
    'equipmentRecord.product.family',
    'equipmentRecord.product.family.productType',
    'equipmentRecord.manufacturerLocation',
    'status',
    'part.partNumber',
    'equipmentRecord',
    'equipmentRecord.product',
    'code',
    'department',
    'category',
    'piQuestionType',
    'legacyId' => 'exact',
    'derogation.status',
    'derogation',
    'equipmentRecord.serialNumber',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'equipmentRecord.serialNumber' => 'partial',
    'equipmentRecord.product.family.productType.englishName' => 'partial',
    'createdBy.firstname' => 'partial',
    'createdBy.lastname' => 'partial',
    'equipmentRecord.manufacturerLocation.name' => 'partial',
    'description' => 'partial',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact'])]
#[ApiFilter(ExistsFilter::class, properties: ['derogation'])]
#[ApiFilter(ColumnsFilter::class)]
#[Legacy\Synchronize(table: 'crabs')]
#[App\Loggable]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            paginationFetchJoinCollection: true,
            normalizationContext: ['groups' => ['location_public', 'crab', 'derogation', 'equipment_record', 'crab_code', 'people_public', 'part', 'expose_legacy', 'crab_departement']],
            forceEager: false,
        ),
        new Post(
            denormalizationContext: ['groups' => ['crab:create', 'part:admin']],
            validationContext: ['groups' => ['Default', 'crab:create']],
        ),
        new Put(),
        new Get(),
        new Get(
            uriTemplate: '/crabs/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CrabFile::class),
                'id' => new Link(fromClass: Crab::class),
            ],
            defaults: ['parentProperty' => 'crab', 'class' => CrabFile::class],
            controller: DownloadController::class,
            name: 'download_crab_file',
        ),
        new Get(
            uriTemplate: '/crabs/{id}/main_file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: CrabMainFile::class),
                'id' => new Link(fromClass: Crab::class),
            ],
            defaults: ['parentProperty' => 'crab', 'class' => CrabMainFile::class],
            controller: DownloadController::class,
            name: 'download_main_crab_file',
        ),
        new Post(
            uriTemplate: '/crabs/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => CrabFile::class],
            controller: UploadController::class,
            deserialize: false,
            name: 'upload_crab_file',
        ),
        new Post(
            uriTemplate: '/crabs/{id}/main_file',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getMainFile', 'class' => CrabMainFile::class],
            controller: UploadController::class,
            deserialize: false,
            name: 'upload_main_crab_file',
        ),
        new Delete(
            uriTemplate: '/crabs/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CrabFile::class),
                'id' => new Link(fromClass: Crab::class),
            ],
            defaults: ['parentProperty' => 'crab', 'class' => CrabFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_CRAB_FILE_DELETE')",
            name: 'delete_crab_file'
        ),
        new Delete(
            uriTemplate: '/crabs/{id}/main_file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: CrabMainFile::class),
                'id' => new Link(fromClass: Crab::class),
            ],
            defaults: ['parentProperty' => 'crab', 'class' => CrabMainFile::class],
            controller: DeleteController::class,
            name: 'delete_main_crab_file'
        ),
        new Delete(security: "is_granted('FEATURE_CRAB_DELETE_VOTER', object)"),
    ],
    routePrefix: 'quality',
    normalizationContext: [
        'groups' => ['crab', 'crab:detail', 'location_public', 'equipment_record', 'crab_code', 'people_public', 'non_conformity', 'process', 'file', 'part', 'expose_legacy', 'product_list', 'derogation', 'crab_departement', 'faq:light'],
    ],
    denormalizationContext: [
        'groups' => ['crab:update_partial'],
    ],
)]
class Crab implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /** @var string */
    final public const CLOSED = 'CLOSED';

    /** @var string */
    final public const TO_FIX = 'TO-FIX';

    /** @var string */
    final public const TO_INSPECT = 'TO-INSPECT';

    /** @var string */
    final public const FOR_DEROGATION = 'FOR-DEROGATION';

    /** @var string */
    final public const PDI_SOL = 'PDI-SOL';

    final public const string PDI_INTERNAL = 'PDI-INTERNAL';

    /** @var string */
    final public const PDI = 'PDI';

    /** @var string */
    final public const PDI_CSC = 'PDI-CSC';

    /** @var string */
    final public const ASSY = 'Assy';

    /** @var string */
    final public const QA = 'QA';

    /** @var string */
    final public const TEST = 'Test';

    /** @var string */
    final public const ECQ = 'ECQ';
    /** @var string */
    final public const PCQ = 'PCQ';
    /** @var string */
    final public const QCQ = 'QCQ';

    #[ORM\Column(type: 'datetime')]
    #[Groups(['crab'])]
    #[Legacy\Column(column: 'dt', transformer: DateTimeToString::class)]
    #[Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Timestampable(on: 'update')]
    public ?\DateTime $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['crab'])]
    #[Legacy\Column(column: 'fix_dt', transformer: DateTimeToString::class)]
    #[Timestampable(on: 'change', field: 'status', value: self::TO_INSPECT)]
    public ?\DateTime $fixedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['crab'])]
    #[Legacy\Column(column: 'insp_dt', transformer: DateTimeToString::class)]
    #[Timestampable(on: 'change', field: 'status', value: self::CLOSED)]
    public ?\DateTime $inspectedAt = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['crab:detail', 'crab:update_partial'])]
    #[Assert\Choice(choices: [self::CLOSED, self::TO_FIX, self::TO_INSPECT, self::FOR_DEROGATION])]
    #[Legacy\Column(column: 'status')]
    public string $status = self::TO_FIX;

    #[ORM\Column(type: 'text')]
    #[Groups(['crab', 'crab:create', 'crab:update_full'])]
    #[Legacy\Column(column: 'dsca', transformer: Utf8ToHtmlEntities::class)]
    public string $description;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['crab:detail', 'crab:update_partial'])]
    #[Legacy\Column(column: 'act', transformer: Utf8ToHtmlEntities::class)]
    public ?string $fixingComments = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['crab:detail', 'crab:update_partial'])]
    public ?string $inspectingComments = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\Choice(choices: [self::ASSY, self::PDI, self::QA, self::TEST, self::PDI_CSC, self::PDI_SOL, self::PDI_INTERNAL])]
    #[Groups(['crab', 'crab:create', 'crab:update_full'])]
    #[Legacy\Column(column: 'opno')]
    public string $category;

    #[ORM\ManyToOne(targetEntity: CrabDepartment::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['crab', 'crab:create', 'crab:update_full'])]
    #[Legacy\Column(column: 'dept', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public CrabDepartment $department;

    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Expression(
        expression: 'value === null or value.getDateShipped() === null',
        message: 'crab.messages.errors.equipment_record_shipped',
        groups: ['crab:create'],
    )]
    #[Groups(['crab', 'crab:create', 'crab:update_full', 'crab:equipment_list', 'crab:write_from_sol'])]
    #[Legacy\Column(column: 'erid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?EquipmentRecord $equipmentRecord;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Groups(['crab'])]
    #[Legacy\Column(column: 'init_emno', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Blameable(on: 'create')]
    public ?People $createdBy = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Groups(['crab'])]
    #[Legacy\Column(column: 'fix_emno', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Blameable(on: 'change', field: 'status', value: self::TO_INSPECT)]
    public ?People $fixedBy = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Groups(['crab'])]
    #[Legacy\Column(column: 'insp_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?People $inspectedBy = null;

    #[ORM\ManyToOne(targetEntity: CrabCode::class)]
    #[Groups(['crab', 'crab:create', 'crab:update_full'])]
    #[Legacy\Column(column: 'code', transformer: ObjectToProperty::class, options: ['property' => 'code'])]
    public ?CrabCode $code = null;

    #[ORM\ManyToOne(targetEntity: NonConformity::class, inversedBy: 'crabs')]
    #[Groups(['crab:detail', 'crab:create', 'crab:update_full'])]
    #[Legacy\Column(column: 'ncrid', transformer: ObjectToProperty::class, options: ['property' => 'id'])]
    #[MaxDepth(1)]
    public ?NonConformity $nonConformity = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['crab:detail', 'crab:create', 'crab:update_full'])]
    public ?int $eapId = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\Derogation', inversedBy: 'crabs')]
    #[Groups(['crab', 'derogation:link'])]
    public ?Derogation $derogation = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\FirstArticleQualification\FirstArticleQualification', fetch: 'EXTRA_LAZY', inversedBy: 'crabs')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['crab:detail', 'crab:create', 'crab:update_partial'])]
    public ?FirstArticleQualification $firstArticleQualification = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['crab:create', 'crab:detail'])]
    public ?int $piQuestionParentId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['crab:create', 'crab:detail'])]
    public ?int $piQuestionId = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['crab'])]
    #[Assert\Choice(choices: [self::ECQ, self::PCQ, self::QCQ])]
    public ?string $piQuestionType = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['crab', 'crab:list'])]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'crab', targetEntity: "App\Entity\Parts\CrabPart", cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'part_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['crab', 'part:admin'])]
    #[Legacy\Column(column: 'pn', transformer: ObjectToProperty::class, options: ['property' => 'partNumber'])]
    private ?CrabPart $part = null;

    /**
     * @var Collection<CrabFile>
     */
    #[ORM\OneToMany(mappedBy: 'crab', targetEntity: 'App\Entity\Quality\CrabFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['crab:detail', 'crab:create', 'crab:update_partial'])]
    private Collection $files;

    /**
     * @var Collection<CrabMainFile>
     */
    #[ORM\OneToMany(mappedBy: 'crab', targetEntity: 'App\Entity\Quality\CrabMainFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['crab:create', 'crab:update_full'])]
    #[Assert\Count(max: 1)]
    private Collection $mainFiles;

    public function __construct()
    {
        $this->mainFiles = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    /**
     * @return Collection<CrabFile>
     */
    #[Groups(['crab:list'])]
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(CrabFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setCrab($this);
        }

        return $this;
    }

    public function removeFile(CrabFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    /**
     * @return Collection<CrabMainFile>
     */
    public function getMainFiles()
    {
        return $this->mainFiles;
    }

    public function addMainFile(CrabMainFile $mainFile): self
    {
        if (!$this->mainFiles->contains($mainFile)) {
            $this->mainFiles->add($mainFile);
            $mainFile->setCrab($this);
        }

        return $this;
    }

    public function removeMainFile(CrabMainFile $mainFile): self
    {
        if ($this->mainFiles->contains($mainFile)) {
            $this->mainFiles->removeElement($mainFile);
        }

        return $this;
    }

    #[Groups(['crab:detail', 'crab:list'])]
    public function getMainFile(): ?CrabMainFile
    {
        if (0 === $this->mainFiles->count()) {
            return null;
        }

        return $this->mainFiles->first();
    }

    public function setMainFile(?CrabMainFile $mainFile): self
    {
        if (null === $mainFile) {
            $this->mainFiles = new ArrayCollection();

            return $this;
        }

        return $this->addMainFile($mainFile);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPart(): ?CrabPart
    {
        return $this->part;
    }

    public function setPart(?CrabPart $part): self
    {
        $this->part = $part;
        if (null !== $part) {
            $part->crab = $this;
        }

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (null === $this->fixingComments && 'TO-INSPECT' === $this->status) {
            $context
                ->buildViolation('Comment is mandatory when fixing CRAB.')
                ->atPath('fixingComments')
                ->addViolation()
            ;
        }
        if (null === $this->inspectingComments && null !== $this->fixingComments && 'CLOSED' === $this->status) {
            $context
                ->buildViolation('Comment is mandatory when inspecting CRAB.')
                ->atPath('inspectingComments')
                ->addViolation()
            ;
        }
    }
}
