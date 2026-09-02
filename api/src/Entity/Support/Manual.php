<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\ZipController;
use App\Controller\Support\ManualPdfChapter4Controller;
use App\Controller\Support\ManualPdfPartsListController;
use App\DataProcessor\Support\ManualDataProcessor;
use App\Doctrine\Mapping\Attributes as App;
use App\Dto\Support\ManualInput;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Filter\SimpleSearchFilter;
use App\Filter\Support\ManualPartDescriptionSearchFilter;
use App\Validator\ManualGroupsGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            openapi: true,
            normalizationContext: ['groups' => ['expose_legacy', 'location_public', 'manual', 'people_public', 'file', 'equipment_record_manuals', 'equipment_serial']],
        ),
        new Get(
            openapi: true,
            security: "is_granted('MANUAL_ACCESS_VOTER', object)",
        ),
        new Get(
            uriTemplate: '/manuals/{id}/pdf/chapter4',
            formats: ['pdf' => 'application/pdf'],
            controller: ManualPdfChapter4Controller::class,
            openapi: true,
            security: "is_granted('MANUAL_ACCESS_VOTER', object)",
            name: 'chapter4_pdf',
        ),
        new Get(
            uriTemplate: '/manuals/{id}/pdf/parts_list',
            formats: ['pdf' => 'application/pdf'],
            controller: ManualPdfPartsListController::class,
            openapi: true,
            name: 'parts_list_pdf',
        ),
        new Get(
            uriTemplate: '/manuals/{id}/zip',
            formats: ['zip' => 'application/zip'],
            controller: ZipController::class,
            name: 'manual_zip',
        ),
        new Post(
            denormalizationContext: [],
            security: "is_granted('FEATURE_MANUAL_ADMIN')",
            input: ManualInput::class,
            processor: ManualDataProcessor::class
        ),
        new Put(security: "is_granted('MANUAL_EDIT_VOTER', object.equipmentRecord)"),
        new Delete(security: "is_granted('MANUAL_EDIT_VOTER', object.equipmentRecord)"),
    ],
    routePrefix: 'support',
    normalizationContext: ['groups' => Manual::NORMALIZATION_GROUPS],
    denormalizationContext: ['groups' => ['manual:write', 'manual_document:write', 'manual_part:write', 'equipment_serial:create']],
    validationContext: ['groups' => ManualGroupsGenerator::class]
)]
#[ORM\Table(name: 'manuals')]
#[ApiFilter(SearchFilter::class, properties: ['id', 'legacyId' => 'exact', 'equipmentRecord.serialNumber' => 'exact', 'documents.parts.partNumber' => 'exact', 'equipmentRecord.manufacturerLocation' => 'exact', 'equipmentRecord.product.family' => 'exact', 'createdBy', 'status'])]
#[ApiFilter(ManualPartDescriptionSearchFilter::class)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id', 'status', 'createdBy.lastname', 'equipmentRecord.serialNumber', 'equipmentSerial.serial'])]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'createdAt' => 'ASC'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => true, 'whitelist' => ['manual_search']])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'manuals')]
class Manual implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /** @var string[] */
    final public const NORMALIZATION_GROUPS = ['expose_legacy', 'component', 'equipment_record_manuals', 'equipment_serial', 'location_public', 'manual', 'manual_document', 'manual_document_category', 'manual_part', 'manual_print', 'people_public', 'file'];

    /** @var string[] */
    final public const STATUS = ['RELEASED', 'PRELIMINARY'];

    /** @var string */
    final public const NONCRITICAL_VALIDATION_GROUP = 'noncritical';

    /** @var string */
    final public const CRITICAL_VALIDATION_GROUP = 'critical';

    /** @var string */
    final public const PUBLISHABLE_VALIDATION_GROUP = 'publishable';

    final public const string USER_VALIDATION_GROUP = 'user';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EquipmentRecord', inversedBy: 'manuals')]
    #[Assert\NotNull(groups: [self::USER_VALIDATION_GROUP])]
    #[Assert\Valid]
    #[Groups(['equipment_record_manuals'])]
    #[MaxDepth(1)]
    public ?EquipmentRecord $equipmentRecord = null;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Support\EquipmentSerial', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\NotNull(groups: [self::USER_VALIDATION_GROUP])]
    #[Groups(['manual'])]
    #[MaxDepth(1)]
    public ?EquipmentSerial $equipmentSerial = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Legacy\Column(column: 'description')]
    #[Groups(['manual', 'manual:write', 'manual_public', 'manual_search'])]
    public ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['manual', 'manual:write', 'manual_public'])]
    #[Legacy\Column(column: 'features')]
    public ?string $features = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Legacy\Column(column: 'lang')]
    #[Groups(['manual', 'manual:write', 'manual_public', 'manual_search', 'manual_document'])]
    public ?string $language = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Assert\Choice(choices: self::STATUS)]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'status')]
    #[Groups(['manual', 'manual:write', 'manual_public', 'manual_search'])]
    public ?string $status = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['manual', 'manual_public', 'manual_search'])]
    #[Legacy\Column(column: 'date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Gedmo\Timestampable(on: 'create')]
    public ?\DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    public ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Groups(['manual'])]
    #[Gedmo\Blameable(on: 'create')]
    public ?People $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id')]
    #[Gedmo\Blameable(on: 'update')]
    public ?People $updatedBy = null;

    /**
     * @var Collection<ManualDocument>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Support\ManualDocument', mappedBy: 'manual', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'manual.document.empty', groups: [self::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Groups(['manual', 'manual:write'])]
    #[MaxDepth(1)]
    private Collection $documents;

    /**
     * @var Collection<ManualPrint>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Support\ManualPrint', mappedBy: 'manual', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['manual', 'manual:write'])]
    #[MaxDepth(1)]
    private Collection $prints;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['manual', 'manual_document', 'manual_public', 'manual_search'])]
    private ?int $id = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->prints = new ArrayCollection();
    }

    public function __clone()
    {
        $clonedDocuments = new ArrayCollection();

        foreach ($this->documents as $document) {
            $clonedDocument = clone $document;
            $clonedDocument->manual = $this;
            $clonedDocuments->add($clonedDocument);
        }

        $this->documents = $clonedDocuments;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<ManualDocument>
     */
    public function getDocuments(): Collection
    {
        return new ArrayCollection($this->documents->getValues());
    }

    public function addDocument(ManualDocument $document): self
    {
        if (!$this->documents->contains($document)) {
            $document->manual = $this;
            $this->documents->add($document);
        }

        return $this;
    }

    public function removeDocument(ManualDocument $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
        }

        return $this;
    }

    /**
     * @return Collection<ManualPrint>
     */
    public function getPrints(): Collection
    {
        return new ArrayCollection($this->prints->getValues());
    }

    public function addPrint(ManualPrint $print): self
    {
        if (!$this->prints->contains($print)) {
            $print->manual = $this;
            $this->prints->add($print);
        }

        return $this;
    }
}
