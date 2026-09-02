<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

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
use App\Controller\Purchasing\SupplierRanking\UploadController;
use App\DataProvider\Purchasing\SupplierRanking\SupplierRankingStatisticsDataProvider;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Dto\Purchasing\SupplierRanking\SupplierRankingStatisticsDto;
use App\Entity\Directory\People;
use App\Entity\Purchasing\Supplier\Supplier;
use App\Filter\ColumnsFilter;
use App\Filter\Purchasing\Supplier\SupplierBuyerFilter;
use App\Filter\Purchasing\Supplier\SupplierMasterBuyerOrderFilter;
use App\Filter\Purchasing\SupplierRanking\AntiCorruptionNotationFilter;
use App\Filter\Purchasing\SupplierRanking\CriteriaOrderFilter;
use App\Filter\Purchasing\SupplierRanking\CybersecurityNotationFilter;
use App\Filter\Purchasing\SupplierRanking\EsgNotationFilter;
use App\Filter\Purchasing\SupplierRanking\HasExpiredFilesFilter;
use App\Filter\Purchasing\SupplierRanking\LocationFilter;
use App\Filter\Purchasing\SupplierRanking\NextReviewAtOrderFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings')]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            paginationFetchJoinCollection: true,
            normalizationContext: ['groups' => ['supplier_ranking', 'location_public', 'people_list', 'classification_list', 'expertise_level_list', 'criteria', 'notation', 'currency', 'supplier_ranking_file', 'file_category']],
            security: "is_granted('FEATURE_SUPPLIER_RANKING_READ') or is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL') or is_granted('ACCESS_VENDOR_USER')",
        ),
        new GetCollection(
            uriTemplate: '/supplier_rankings/statistics',
            normalizationContext: ['groups' => ['supplier_ranking_statistics']],
            security: "is_granted('FEATURE_SUPPLIER_RANKING_READ') or is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL') or is_granted('ACCESS_VENDOR_USER')",
            output: SupplierRankingStatisticsDto::class,
            name: 'supplier_ranking_statistics',
            provider: SupplierRankingStatisticsDataProvider::class,
            paginationEnabled: false,
        ),
        new Put(
            denormalizationContext: ['groups' => ['supplier_ranking:update', 'notation:update']],
            security: "is_granted('SUPPLIER_RANKING_UPDATE_VOTER', object) or (is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL') and is_granted('FEATURE_SUPPLIER_RANKING_UPDATE'))",
        ),
        new Delete(
            uriTemplate: '/supplier_rankings/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: SupplierRankingFile::class),
                'id' => new Link(fromClass: SupplierRanking::class),
            ],
            defaults: ['parentProperty' => 'supplierRanking', 'class' => SupplierRankingFile::class],
            controller: DeleteController::class,
            security: "is_granted('SUPPLIER_RANKING_UPDATE_VOTER', object) or (is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL') and is_granted('FEATURE_SUPPLIER_RANKING_UPDATE'))",
            name: 'delete_supplier_ranking_file',
        ),
        new Get(
            normalizationContext: ['groups' => ['supplier_ranking', 'location_public', 'people_list', 'classification', 'expertise_level', 'criteria', 'notation', 'currency', 'supplier_ranking_file', 'file:light', 'file_category']],
            security: "is_granted('SUPPLIER_RANKING_READ_VOTER', object) or is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL')",
        ),
        new Get(
            uriTemplate: '/supplier_rankings/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: SupplierRankingFile::class),
                'id' => new Link(fromClass: SupplierRanking::class),
            ],
            defaults: ['parentProperty' => 'supplierRanking', 'class' => SupplierRankingFile::class],
            controller: DownloadController::class,
            security: "is_granted('SUPPLIER_RANKING_READ_VOTER', object) or is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL')",
            name: 'download_supplier_ranking_file'
        ),
        new Post(
            uriTemplate: '/supplier_rankings/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => SupplierRankingFile::class],
            controller: UploadController::class,
            security: "is_granted('SUPPLIER_RANKING_UPDATE_VOTER', object) or (is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL') and is_granted('FEATURE_SUPPLIER_RANKING_UPDATE'))",
            deserialize: false,
            name: 'upload_supplier_ranking_file'
        ),
    ],
    routePrefix: '/purchasing/supplier_ranking',
    normalizationContext: ['groups' => ['supplier_ranking', 'location_public', 'people_list', 'classification', 'expertise_level', 'criteria', 'notation', 'currency']],
    order: ['lastReviewAt' => 'desc'],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'supplier' => 'exact',
    'classification' => 'exact',
    'expertiseLevel' => 'exact',
    'supplier.code' => 'partial',
    'supplier.name' => 'partial',
    'lastReviewBy',
    'lastScreeningBy',
    'classification.isSupplierApproved' => 'exact',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'supplier.code' => 'partial',
    'supplier.name' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'supplier.code',
    'supplier.name',
    'lastReviewAt',
    'classification.name',
    'lastScreeningAt',
    'supplier.location.name',
    'revenue',
    'supplier.currency.name',
    'classification.isSupplierApproved',
    'nextReviewAt',
    'expertiseLevel.name',
])]
#[ApiFilter(NextReviewAtOrderFilter::class)]
#[ApiFilter(CriteriaOrderFilter::class)]
#[ApiFilter(EsgNotationFilter::class)]
#[ApiFilter(HasExpiredFilesFilter::class)]
#[ApiFilter(AntiCorruptionNotationFilter::class)]
#[ApiFilter(CybersecurityNotationFilter::class)]
#[ApiFilter(DateFilter::class, properties: ['lastReviewAt', 'lastScreeningAt'])]
#[ApiFilter(ExistsFilter::class, properties: ['revenue', 'disabledAt'])]
#[ApiFilter(LocationFilter::class)]
#[ApiFilter(SupplierBuyerFilter::class)]
#[ApiFilter(SupplierMasterBuyerOrderFilter::class)]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
class SupplierRanking implements \Stringable
{
    use LegacyIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: Classification::class)]
    #[Transferable(manager: 'manager.classification')]
    #[Groups(['supplier_ranking', 'supplier_ranking:update'])]
    public ?Classification $classification = null;

    #[ORM\ManyToOne(targetEntity: ExpertiseLevel::class)]
    #[Transferable(manager: 'manager.expertise_level')]
    #[Groups(['supplier_ranking', 'supplier_ranking:update'])]
    public ?ExpertiseLevel $expertiseLevel = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups('supplier_ranking')]
    public ?\DateTimeInterface $lastReviewAt = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'last_review_by')]
    #[Gedmo\Blameable(on: 'change', field: ['lastReviewAt'])]
    #[Groups('supplier_ranking')]
    public ?People $lastReviewBy = null;

    /**
     * Manual date to specify when a screening investigation was done.
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['supplier_ranking', 'supplier_ranking:update'])]
    public ?\DateTimeInterface $lastScreeningAt = null;

    /**
     * User who investigate screening. Using user who update lastScreeningDate.
     */
    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'last_screening_by', nullable: true)]
    #[Groups('supplier_ranking')]
    public ?People $lastScreeningBy = null;

    /**
     * Turnover data from LN.
     */
    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups('supplier_ranking')]
    public ?float $revenue = null;

    /**
     * Count of non conformities created for supplier during the period of filter range date.
     */
    public int $nonConformityCount = 0;

    /**
     * Denormalization group depending on user type.
     * Vendor user = supplier:light
     * Default user = supplier.
     *
     * @see SupplierRankingContextBuilder
     */
    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(unique: true, nullable: false)]
    #[Groups('supplier_ranking')]
    #[MaxDepth(1)]
    public ?Supplier $supplier = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['supplier_ranking'])]
    public ?\DateTime $disabledAt = null;

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups('supplier_ranking')]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'supplierRanking', targetEntity: Notation::class, cascade: ['persist', 'remove'])]
    #[Groups(['supplier_ranking', 'supplier_ranking:update'])]
    private Collection $notations;

    #[ORM\OneToMany(mappedBy: 'supplierRanking', targetEntity: SupplierRankingFile::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['supplier_ranking'])]
    private Collection $files;

    public function __construct()
    {
        $this->notations = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBusinessPartnerCode(): ?string
    {
        return $this->getSupplierName();
    }

    public function getSupplierNumber(): string
    {
        return $this->supplier?->code;
    }

    public function getSupplierName(): ?string
    {
        return $this->supplier?->name;
    }

    #[Groups('supplier_ranking')]
    public function isSupplierApproved(): bool
    {
        return $this->classification ? $this->classification->isSupplierApproved : false;
    }

    /**
     * @return Collection<Notation>
     */
    public function getNotations(): Collection
    {
        return $this->notations;
    }

    public function setNotations(Collection $notations): self
    {
        $this->notations = $notations;

        return $this;
    }

    public function addNotation(Notation $supplierRankingCriteria): self
    {
        if (!$this->notations->contains($supplierRankingCriteria)) {
            $this->notations->add($supplierRankingCriteria);
            $supplierRankingCriteria->supplierRanking = $this;
        }

        return $this;
    }

    public function removeNotation(Notation $supplierRankingCriteria): self
    {
        if ($this->notations->contains($supplierRankingCriteria)) {
            $this->notations->removeElement($supplierRankingCriteria);
        }

        return $this;
    }

    /**
     * @return Collection<SupplierRankingFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(SupplierRankingFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->supplierRanking = $this;
        }

        return $this;
    }

    public function removeFile(SupplierRankingFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            $file->supplierRanking = null;
        }

        return $this;
    }

    public function getPeriodicity(): ?Periodicity
    {
        if (null === $this->classification || null === $this->expertiseLevel) {
            return null;
        }

        foreach ($this->classification->getPeriodicityByExpertiseLevels() as $periodicity) {
            if ($periodicity->expertiseLevel === $this->expertiseLevel) {
                return $periodicity;
            }
        }

        return null;
    }

    #[Groups('supplier_ranking')]
    public function getNextReviewAt(): ?\DateTime
    {
        if (!$this->lastReviewAt instanceof \DateTime || null === ($periodicity = $this->getPeriodicity())) {
            return null;
        }

        return (clone $this->lastReviewAt)->modify(\sprintf('+%d months', $periodicity->months));
    }
}
