<?php

declare(strict_types=1);

namespace App\Entity\Support;

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
use App\Controller\Support\ManualDocumentPdfController;
use App\Entity\Directory\People;
use App\Factory\VaultFileInterface;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\ToString;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    operations: [
        new GetCollection(
            openapi: true,
            normalizationContext: ['groups' => ['expose_legacy']]
        ),
        new Put(security: "is_granted('FEATURE_MANUAL_ADMIN')"),
        new Get(
            openapi: true,
            security: "is_granted('EQUIPMENT_ACCESS_VOTER', object.manual.equipmentRecord)",
        ),
        new Get(
            uriTemplate: '/manual_documents/{id}/pdf/document',
            formats: ['pdf' => 'application/pdf'],
            controller: ManualDocumentPdfController::class,
            openapi: true,
            security: "is_granted('EQUIPMENT_ACCESS_VOTER', object.manual.equipmentRecord)",
            name: 'manual_document_pdf',
        ),
        new Get(
            uriTemplate: '/manual_documents/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ManualDocumentFile::class),
                'id' => new Link(fromClass: ManualDocument::class),
            ],
            defaults: ['parentProperty' => 'manualDocument', 'class' => ManualDocumentFile::class],
            controller: DownloadController::class,
            openapi: true,
            security: "is_granted('DOWNLOAD_MANUAL_DOCUMENT_FILE_VOTER', object)",
            name: 'download_manual_document_file',
        ),
        new Post(
            uriTemplate: '/manual_documents/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getDocument', 'class' => ManualDocumentFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_MANUAL_ADMIN')",
            deserialize: false,
            name: 'upload_manual_document_file',
        ),
        new Delete(
            uriTemplate: '/manual_documents/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ManualDocumentFile::class),
                'id' => new Link(fromClass: ManualDocument::class),
            ],
            defaults: ['parentProperty' => 'manualDocument', 'class' => ManualDocumentFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_MANUAL_ADMIN')",
            name: 'delete_manual_document_file',
        ),
    ],
    routePrefix: 'support',
    normalizationContext: ['groups' => ['equipment_record_manuals', 'expose_legacy', 'location_public', 'manual_document', 'manual_document_category', 'manual_part', 'file', 'people_public']],
    denormalizationContext: ['groups' => ['manual_document:write', 'manual_part:write']],
)]
#[ORM\Entity]
#[ORM\Table(name: 'manual_documents')]
#[ApiFilter(SearchFilter::class, properties: ['id', 'legacyId' => 'exact', 'manual' => 'exact', 'parts.partNumber' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['parts.description' => 'partial', 'parts.otherDescription' => 'partial'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => true, 'whitelist' => ['manual_document', 'manual_document_category']])]
#[Legacy\Synchronize(table: 'manuals_diag')]
#[Legacy\ExtraTable(
    table: 'manuals_docs',
    properties: [
        new Legacy\ExtraTableColumn(column: 'parent_id', property: 'manual', transformer: ObjectToProperty::class, options: ['property' => 'legacyId']),
        new Legacy\ExtraTableColumn(column: 'item', property: 'position', transformer: ToString::class),
        new Legacy\ExtraTableColumn(column: 'doc_num', property: 'legacyId', key: true)]
)]
class ManualDocument implements LegacyIdInterface, VaultFileInterface
{
    use LegacyIdentifierTrait;

    final public const DOC_TYPE = [
        'ELEC SCHEM',
        'FLOW SCHEM',
        'HYD SCHEM',
        'MANUAL SECTION',
        'MANUAL:APPENDIX',
        'MANUAL:OEM LIT',
        self::MANUAL_SECTION,
        'MANUAL:TOC',
        self::PARTS_DIAGRAM,
    ];

    final public const MANUAL_SECTION = 'MANUAL:SECTION';
    final public const PARTS_DIAGRAM = 'PARTS DIAGRAM';

    /** @var string[] */
    final public const PDF_FORMAT_VALUES = ['document'];

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\Manual', inversedBy: 'documents')]
    #[Assert\NotNull]
    #[Groups(['manual_document'])]
    #[MaxDepth(1)]
    public ?Manual $manual = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Groups(['manual_document', 'manual_document:write'])]
    public int $position;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['manual_document', 'manual_document:write'])]
    #[Legacy\Column(column: 'factory_num', transformer: Utf8ToHtmlEntities::class)]
    public ?string $factoryNumber = null;

    #[ORM\Column(type: 'string', length: 11, nullable: true)]
    #[Groups(['manual_document', 'manual_document:write'])]
    #[Legacy\Column(column: 'rev', transformer: Utf8ToHtmlEntities::class)]
    public ?string $revision = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\ManualDocumentCategory')]
    #[Legacy\Column(column: 'category', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    #[Groups(['manual_document', 'manual_document:write', 'manual_part:recommended_spare_part_list'])]
    #[Assert\NotNull(groups: ['Default', Manual::CRITICAL_VALIDATION_GROUP], payload: ['severity' => 'critical'])]
    public ?ManualDocumentCategory $category = null;

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Choice(choices: self::DOC_TYPE, groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Assert\NotNull(groups: ['Default', Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    #[Legacy\Column(column: 'doc_type', transformer: Utf8ToHtmlEntities::class)]
    #[Groups(['manual_document', 'manual_document:write'])]
    public string $type;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['manual_document', 'manual_document:write'])]
    #[Legacy\Column(column: 'endescription', transformer: Utf8ToHtmlEntities::class)]
    public ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['manual_document', 'manual_document:write'])]
    #[Legacy\Column(column: 'frdescription', transformer: Utf8ToHtmlEntities::class)]
    public ?string $otherDescription = null;

    #[Groups(['manual_document', 'manual_document:write'])]
    public float $quantity = 0.0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['manual_document'])]
    #[Gedmo\Timestampable(on: 'create')]
    public ?\DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    public ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Groups(['manual_document'])]
    #[Gedmo\Blameable(on: 'create')]
    public ?People $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id')]
    #[Gedmo\Blameable(on: 'update')]
    public ?People $updatedBy = null;

    /**
     * @var Collection<ManualPart>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Support\ManualPart', mappedBy: 'document', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(['manual_document', 'manual_document:write'])]
    private Collection $parts;

    /**
     * @var Collection<ManualDocumentFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Support\ManualDocumentFile', mappedBy: 'manualDocument', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Assert\Count(max: 1)]
    #[Assert\NotNull(groups: [Manual::NONCRITICAL_VALIDATION_GROUP])]
    #[Groups(['manual_document:write'])]
    private Collection $files;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['manual_document'])]
    private int $id;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->parts = new ArrayCollection();
    }

    public function __clone()
    {
        $clonedParts = new ArrayCollection();
        foreach ($this->parts as $part) {
            $clonedPart = clone $part;
            $clonedPart->document = $this;
            $clonedParts->add($clonedPart);
        }
        $this->parts = $clonedParts;

        $clonedFiles = new ArrayCollection();
        foreach ($this->files as $file) {
            $clonedFile = clone $file;
            $clonedFile->setManualDocument($this);
            $clonedFiles->add($clonedFile);
        }
        $this->files = $clonedFiles;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParts(): Collection
    {
        return new ArrayCollection($this->parts->getValues());
    }

    public function addPart(ManualPart $part): self
    {
        if (!$this->parts->contains($part)) {
            $part->document = $this;
            $this->parts->add($part);
        }

        return $this;
    }

    public function removePart(ManualPart $part): self
    {
        if ($this->parts->contains($part)) {
            $this->parts->removeElement($part);
        }

        return $this;
    }

    #[Groups('manual_document')]
    public function getDocument(): ?ManualDocumentFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }

    public function setDocument(?ManualDocumentFile $file): self
    {
        if (null === $file) {
            $this->files = new ArrayCollection();

            return $this;
        }

        return $this->addFile($file);
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(ManualDocumentFile $file): self
    {
        $file->setManualDocument($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(ManualDocumentFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    #[Assert\Callback(groups: [Manual::CRITICAL_VALIDATION_GROUP], payload: ['severity' => 'critical'])]
    public function validateCritical(ExecutionContextInterface $context): void
    {
        if ('MANUAL:SECTION' === $this->type && $this->files->isEmpty()) {
            $context
                ->buildViolation(\sprintf('Pdf file not found for this item %s revision %s', $this->factoryNumber, mb_trim($this->revision)))
                ->addViolation();
        }
    }

    #[Assert\Callback(groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
    public function validateNonCritical(ExecutionContextInterface $context): void
    {
        if ($this->files->isEmpty()) {
            $context
                ->buildViolation(\sprintf('%s file not found for this item %s revision %s', 'MANUAL:SECTION' === $this->type && $this->files->isEmpty() ? 'Pdf' : 'Jpg', $this->factoryNumber, mb_trim($this->revision)))
                ->addViolation();
        }

        if (self::PARTS_DIAGRAM === $this->type && 0 > $this->quantity) {
            $context
                ->buildViolation('This value should be greater than 0.')
                ->addViolation();
        }
    }

    public function getPartNumber(): string
    {
        return $this->factoryNumber;
    }

    public function getRevision(): string
    {
        return $this->revision;
    }

    public function getFileClass(): string
    {
        return ManualDocumentFile::class;
    }
}
