<?php

declare(strict_types=1);

namespace App\Entity\Sales;

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
use App\Entity\Finance\Currency;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\Sales\CompetitorPricingRepository')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['competitor_pricing', 'expose_legacy', 'people_public', 'competitor_public', 'currency', 'incoterm']]),
        new Put(security: "is_granted('FEATURE_COMPETITOR_PRICING_WRITE')"),
        new Delete(
            uriTemplate: '/competitor_pricings/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'competitorPricingFiles', fromClass: CompetitorPricingFile::class),
                'id' => new Link(fromClass: CompetitorPricing::class),
            ],
            defaults: ['parentProperty' => 'competitorPricing', 'class' => CompetitorPricingFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_COMPETITOR_PRICING_WRITE', object)",
            name: 'delete_competitor_pricing_file',
        ),
        new Delete(security: "is_granted('COMPETITOR_PRICING_DELETE_VOTER', object) or is_granted('MOO_CPR')"),
        new Post(
            denormalizationContext: ['groups' => ['competitor_pricing_write']],
            security: "is_granted('FEATURE_COMPETITOR_PRICING_WRITE')",
        ),
        new Post(
            uriTemplate: '/competitor_pricings/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getCompetitorPricingFiles', 'class' => CompetitorPricingFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_COMPETITOR_PRICING_WRITE', object)",
            deserialize: false,
            name: 'upload_competitor_pricing_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/competitor_pricings/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'competitorPricingFiles', fromClass: CompetitorPricingFile::class),
                'id' => new Link(fromClass: CompetitorPricing::class),
            ],
            defaults: ['parentProperty' => 'competitorPricing', 'class' => CompetitorPricingFile::class],
            controller: DownloadController::class,
            name: 'download_competitor_pricing_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['competitor_pricing_detail', 'competitor_pricing', 'expose_legacy', 'people_public', 'file', 'competitor_public', 'location_public', 'customer_public', 'catalogue_public', 'sales_forecast_public', 'currency', 'incoterm']],
)]
#[ORM\Table(name: 'competitor_pricings')]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact', 'forecastClosure' => 'exact', 'forecastClosure.salesForecast' => 'exact', 'forecastClosure.salesForecast.product' => 'exact', 'forecastClosure.status' => 'partial', 'competitor' => 'exact'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'cpr')]
class CompetitorPricing
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ForecastClosure', inversedBy: 'competitorPricings')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?ForecastClosure $forecastClosure = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\Type('DateTimeInterface')]
    #[Groups(['competitor_pricing_detail'])]
    #[Legacy\Column(column: 'created', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Blameable(on: 'create')]
    private People $poster;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail', 'competitor_pricing_write'])]
    #[Assert\NotNull]
    #[Assert\Type('DateTimeInterface')]
    #[Legacy\Column(column: 'qdate', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private \DateTimeInterface $quotationDate;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Competitor')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'competitor', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private Competitor $competitor;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'model', transformer: Utf8ToHtmlEntities::class)]
    private ?string $competitorModel = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'options', transformer: Utf8ToHtmlEntities::class)]
    private ?string $competitorOptions = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'qty')]
    private ?int $quantity = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'price')]
    private ?float $price = null;

    #[ORM\Column(name: 'currency', type: 'string', length: 10, nullable: true)]
    private ?string $baanCurrency = null;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'currency', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Currency $currency = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'exchange_rate')]
    private ?float $exchangeRate = null;

    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 3, max: 3)])]
    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $incoterms = null;

    #[ORM\ManyToOne(targetEntity: Incoterm::class)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'inco_terms', transformer: ObjectToProperty::class, options: ['property' => 'code'])]
    private ?Incoterm $incoterm = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'inco_loc')]
    private ?string $incotermsLocation = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['competitor_pricing_detail', 'competitor_pricing', 'sales_forecast_detail', 'competitor_pricing_write'])]
    #[Legacy\Column(column: 'markup_percent')]
    private ?int $markupPercentage = null;

    /**
     * @var Collection<CompetitorPricingFile>
     */
    #[ORM\OneToMany(mappedBy: 'competitorPricing', targetEntity: 'App\Entity\Sales\CompetitorPricingFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['competitor_pricing_detail'])]
    private Collection $competitorPricingFiles;

    #[Groups(['competitor_pricing_write'])]
    private bool $last = false;

    public function __construct()
    {
        $this->competitorPricingFiles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getForecastClosure(): ?ForecastClosure
    {
        return $this->forecastClosure;
    }

    public function setForecastClosure(?ForecastClosure $forecastClosure): self
    {
        $this->forecastClosure = $forecastClosure;

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

    public function getPoster(): People
    {
        return $this->poster;
    }

    public function setPoster(People $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getQuotationDate(): \DateTimeInterface
    {
        return $this->quotationDate;
    }

    public function setQuotationDate(\DateTimeInterface $quotationDate): self
    {
        $this->quotationDate = $quotationDate;

        return $this;
    }

    public function getCompetitor(): Competitor
    {
        return $this->competitor;
    }

    public function setCompetitor(Competitor $competitor): self
    {
        $this->competitor = $competitor;

        return $this;
    }

    public function getCompetitorModel(): ?string
    {
        return $this->competitorModel;
    }

    public function setCompetitorModel(?string $competitorModel): self
    {
        $this->competitorModel = $competitorModel;

        return $this;
    }

    public function getCompetitorOptions(): ?string
    {
        return $this->competitorOptions;
    }

    public function setCompetitorOptions(?string $competitorOptions): self
    {
        $this->competitorOptions = $competitorOptions;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getBaanCurrency(): ?string
    {
        return $this->baanCurrency;
    }

    public function setBaanCurrency(?string $baanCurrency): self
    {
        $this->baanCurrency = $baanCurrency;

        return $this;
    }

    public function getExchangeRate(): ?float
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(?float $exchangeRate): self
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    public function getIncoterms(): ?string
    {
        return $this->incoterms;
    }

    public function setIncoterms(?string $incoterms): self
    {
        $this->incoterms = $incoterms;

        return $this;
    }

    public function getIncotermsLocation(): ?string
    {
        return $this->incotermsLocation;
    }

    public function setIncotermsLocation(?string $incotermsLocation): self
    {
        $this->incotermsLocation = $incotermsLocation;

        return $this;
    }

    public function getMarkupPercentage(): ?int
    {
        return $this->markupPercentage;
    }

    public function setMarkupPercentage(?int $markupPercentage): self
    {
        $this->markupPercentage = $markupPercentage;

        return $this;
    }

    public function getCompetitorPricingFiles()
    {
        return $this->competitorPricingFiles;
    }

    public function addCompetitorPricingFile(CompetitorPricingFile $competitorPricingFile)
    {
        $competitorPricingFile->setCompetitorPricing($this);
        $this->competitorPricingFiles->add($competitorPricingFile);

        return $this;
    }

    public function removeCompetitorPricingFile(CompetitorPricingFile $competitorPricingFile)
    {
        $this->competitorPricingFiles->removeElement($competitorPricingFile);

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null !== $this->price && null === $this->currency) {
            $context
                ->buildViolation('Currency is mandatory if you entered a price')
                ->atPath('currency')
                ->addViolation()
            ;
        }
    }

    public function isLast(): bool
    {
        return $this->last;
    }

    public function setLast(bool $last): self
    {
        $this->last = $last;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getIncoterm(): ?Incoterm
    {
        return $this->incoterm;
    }

    public function setIncoterm(?Incoterm $incoterm): self
    {
        $this->incoterm = $incoterm;

        return $this;
    }
}
