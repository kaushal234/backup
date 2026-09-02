<?php

declare(strict_types=1);

namespace App\Entity\Sales\MarketIntelligence;

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
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Entity\ConfidentialInterface;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\PositionLevel;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ProductType;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['market_intelligence', 'market_intelligence_type', 'people_public', 'customer_list', 'competitor_list', 'catalogue_type_list', 'file', 'division']]),
        new Delete(security: "is_granted('MARKET_INTELLIGENCE_ADMIN_VOTER', object)"),
        new Delete(
            uriTemplate: '/market_intelligences/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'marketIntelligenceFiles', fromClass: MarketIntelligenceFile::class),
                'id' => new Link(fromClass: MarketIntelligence::class),
            ],
            defaults: ['parentProperty' => 'marketIntelligence', 'class' => MarketIntelligenceFile::class],
            controller: DeleteController::class,
            security: "is_granted('MARKET_INTELLIGENCE_FILE_DELETION_VOTER', object)",
            name: 'delete_market_intelligence_file'
        ),
        new Put(denormalizationContext: ['groups' => ['market_intelligence:edit']], security: "is_granted('MARKET_INTELLIGENCE_ADMIN_VOTER', object)"),
        new Post(),
        new Post(
            uriTemplate: '/market_intelligences/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getMarketIntelligenceFiles', 'class' => MarketIntelligenceFile::class],
            controller: UploadController::class,
            security: "is_granted('MARKET_INTELLIGENCE_VIEW_VOTER', object)",
            deserialize: false,
            name: 'upload_market_intelligence_file',
        ),
        new Get(security: "is_granted('MARKET_INTELLIGENCE_VIEW_VOTER', object)"),
        new Get(
            uriTemplate: '/market_intelligences/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'marketIntelligenceFiles', fromClass: MarketIntelligenceFile::class),
                'id' => new Link(fromClass: MarketIntelligence::class),
            ],
            defaults: ['parentProperty' => 'marketIntelligence', 'class' => MarketIntelligenceFile::class],
            controller: DownloadController::class,
            security: "is_granted('MARKET_INTELLIGENCE_VIEW_VOTER', object)",
            name: 'download_market_intelligence_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['market_intelligence:detail', 'market_intelligence_type', 'people_public', 'customer', 'competitor', 'catalogue_type', 'file', 'division']],
    denormalizationContext: ['groups' => ['market_intelligence:create']],
    paginationItemsPerPage: 10,
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'partial', 'legacyId' => 'exact', 'customers' => 'exact', 'competitors' => 'exact', 'productTypes' => 'exact', 'type' => 'exact', 'poster' => 'exact', 'suppliers' => 'partial'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['market_intelligence:list']])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['description' => 'partial', 'shortDescription' => 'partial'])]
#[App\Loggable]
class MarketIntelligence implements ConfidentialInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['market_intelligence', 'market_intelligence:detail', 'market_intelligence:list'])]
    private int $id;

    #[ORM\Column(name: 'legacy_id', type: 'integer', nullable: true)]
    #[Groups(['market_intelligence:detail'])]
    private ?int $legacyId = null;

    #[ORM\ManyToOne(targetEntity: MarketIntelligenceType::class)]
    #[ORM\JoinColumn]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence', 'market_intelligence:detail'])]
    private ?MarketIntelligenceType $type = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['market_intelligence', 'market_intelligence:detail'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 75)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 75)]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence', 'market_intelligence:detail', 'market_intelligence:list'])]
    private string $shortDescription;

    #[ORM\Column(type: 'text')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence:detail'])]
    private string $description;

    /**
     * @var Collection<Customer>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\Customer')]
    #[MaxDepth(2)]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence', 'market_intelligence:detail'])]
    private Collection $customers;

    /**
     * @var Collection<Competitor>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\Competitor')]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence', 'market_intelligence:detail'])]
    private Collection $competitors;

    /**
     * @var Collection<PositionLevel>
     */
    #[ORM\ManyToMany(targetEntity: PositionLevel::class)]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit_admin', 'market_intelligence', 'market_intelligence:detail'])]
    private Collection $positionLevels;

    /**
     * @var Collection<Division>
     */
    #[ORM\ManyToMany(targetEntity: Division::class)]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence', 'market_intelligence:detail'])]
    private Collection $divisions;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence', 'market_intelligence:detail'])]
    private array $suppliers = [];

    /**
     * @var Collection<ProductType>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ProductType')]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence', 'market_intelligence:detail'])]
    private Collection $productTypes;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['market_intelligence', 'market_intelligence:detail'])]
    #[Gedmo\Blameable(on: 'create')]
    private ?People $poster = null;

    /**
     * @var Collection<MarketIntelligenceFile>
     */
    #[ORM\OneToMany(mappedBy: 'marketIntelligence', targetEntity: MarketIntelligenceFile::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['market_intelligence:detail', 'market_intelligence'])]
    private Collection $marketIntelligenceFiles;

    /**
     * @var Collection<MarketIntelligence>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'marketIntelligencesLinked')]
    #[Groups(['market_intelligence', 'market_intelligence:create', 'market_intelligence:edit', 'market_intelligence:detail'])]
    #[Exclude]
    private Collection $marketIntelligencesLinkedTo;

    /**
     * @var Collection<MarketIntelligence>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'marketIntelligencesLinkedTo')]
    #[ORM\JoinTable(name: 'market_intelligence_dependencies')]
    #[ORM\JoinColumn(name: 'market_intelligence_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'market_intelligence_linked_id', referencedColumnName: 'id')]
    #[MaxDepth(1)]
    #[Groups(['market_intelligence', 'market_intelligence:create', 'market_intelligence:edit', 'market_intelligence:detail'])]
    #[Exclude]
    private Collection $marketIntelligencesLinked;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Groups(['market_intelligence:create', 'market_intelligence:edit', 'market_intelligence:detail'])]
    private ?string $url = null;

    public function __construct()
    {
        $this->marketIntelligenceFiles = new ArrayCollection();
        $this->customers = new ArrayCollection();
        $this->competitors = new ArrayCollection();
        $this->productTypes = new ArrayCollection();
        $this->marketIntelligencesLinked = new ArrayCollection();
        $this->marketIntelligencesLinkedTo = new ArrayCollection();
        $this->positionLevels = new ArrayCollection();
        $this->divisions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): ?MarketIntelligenceType
    {
        return $this->type;
    }

    public function setType(?MarketIntelligenceType $type): self
    {
        $this->type = $type;

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

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        $this->customers->add($customer);

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        $this->customers->removeElement($customer);

        return $this;
    }

    public function getCompetitors(): Collection
    {
        return $this->competitors;
    }

    public function addCompetitor(Competitor $competitor): self
    {
        $this->competitors->add($competitor);

        return $this;
    }

    public function removeCompetitor(Competitor $competitor): self
    {
        $this->competitors->removeElement($competitor);

        return $this;
    }

    public function getProductTypes(): Collection
    {
        return $this->productTypes;
    }

    public function addProductType(ProductType $productType): self
    {
        $this->productTypes->add($productType);

        return $this;
    }

    public function removeProductType(ProductType $productType): self
    {
        $this->productTypes->removeElement($productType);

        return $this;
    }

    public function getMarketIntelligencesLinkedTo(): Collection
    {
        return $this->marketIntelligencesLinkedTo;
    }

    public function addMarketIntelligencesLinkedTo(self $marketIntelligence): self
    {
        $this->marketIntelligencesLinkedTo->add($marketIntelligence);

        return $this;
    }

    public function removeMarketIntelligencesLinkedTo(self $marketIntelligence): self
    {
        $this->marketIntelligencesLinkedTo->removeElement($marketIntelligence);

        return $this;
    }

    public function getMarketIntelligencesLinked(): Collection
    {
        return $this->marketIntelligencesLinked;
    }

    public function addMarketIntelligencesLinked(self $marketIntelligence): self
    {
        $this->marketIntelligencesLinked->add($marketIntelligence);
        $marketIntelligence->addMarketIntelligencesLinkedTo($this);

        return $this;
    }

    public function removeMarketIntelligencesLinked(self $marketIntelligence): self
    {
        $this->marketIntelligencesLinked->removeElement($marketIntelligence);
        $marketIntelligence->removeMarketIntelligencesLinkedTo($this);

        return $this;
    }

    public function getPoster(): ?People
    {
        return $this->poster;
    }

    public function setPoster(?People $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getMarketIntelligenceFiles(): Collection
    {
        return $this->marketIntelligenceFiles;
    }

    public function addMarketIntelligenceFile(MarketIntelligenceFile $marketIntelligenceFile): self
    {
        $marketIntelligenceFile->setMarketIntelligence($this);
        $this->marketIntelligenceFiles->add($marketIntelligenceFile);

        return $this;
    }

    public function removeMarketIntelligenceFile(MarketIntelligenceFile $marketIntelligenceFile): self
    {
        $this->marketIntelligenceFiles->removeElement($marketIntelligenceFile);

        return $this;
    }

    public function getLegacyId(): ?int
    {
        return $this->legacyId;
    }

    /**
     * @return $this
     */
    public function setLegacyId(?int $legacyId)
    {
        $this->legacyId = $legacyId;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSuppliers(): array
    {
        return $this->suppliers;
    }

    public function setSuppliers(array $suppliers): self
    {
        $this->suppliers = $suppliers;

        return $this;
    }

    /**
     * @return Collection|PositionLevel[]
     */
    public function getPositionLevels(): Collection
    {
        return $this->positionLevels;
    }

    public function addPositionLevel(PositionLevel $positionLevel): self
    {
        $this->positionLevels->add($positionLevel);

        return $this;
    }

    public function removePositionLevel(PositionLevel $positionLevel): self
    {
        $this->positionLevels->removeElement($positionLevel);

        return $this;
    }

    public function getDivisions(): Collection
    {
        return $this->divisions;
    }

    public function addDivision(Division $division): self
    {
        $this->divisions->add($division);

        return $this;
    }

    public function removeDivision(Division $division): self
    {
        $this->divisions->removeElement($division);

        return $this;
    }

    public function isConfidential(): bool
    {
        return !$this->positionLevels->isEmpty();
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (
            $this->getProductTypes()->isEmpty()
            && $this->getCompetitors()->isEmpty()
            && $this->getCustomers()->isEmpty()
            && empty($this->getSuppliers())
            && MarketIntelligenceType::MISCELLANEOUS_INFORMATION !== $this->type->name
        ) {
            $context->buildViolation('One of Customer, Competitor, Supplier or Product Type should not be null.')
                ->atPath('customers')
                ->addViolation();
        }

        if ($this->getMarketIntelligencesLinked()->contains($this) || $this->getMarketIntelligencesLinkedTo()->contains($this)) {
            $context->buildViolation('A MIM can\'t reference itself')
                ->atPath('marketIntelligencesLinked')
                ->addViolation();
        }
    }
}
