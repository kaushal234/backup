<?php

declare(strict_types=1);

namespace App\Entity\Sales;

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
use App\Entity\Finance\Currency;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\KeyValue;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Sales\ForecastClosureRepository')]
#[ApiResource(
    operations: [
        new GetCollection(
            paginationFetchJoinCollection: true,
            normalizationContext: ['groups' => ['forecast_closure', 'expose_legacy', 'people_public', 'sales_forecast_public', 'location_public', 'customer_public', 'competitor_public', 'catalogue_public', 'network']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['forecast_closure_edit']],
            securityPostDenormalize: "is_granted('FORECAST_CLOSURE_WRITE_VOTER', previous_object)",
        ),
        new Delete(
            security: "is_granted('FEATURE_SALES_FORECAST_ADMIN_EDIT', object.getSalesForecast()) or is_granted('MOO_FCR')",
        ),
        new Delete(
            uriTemplate: '/forecast_closures/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'forecastClosureFiles', fromClass: ForecastClosureFile::class),
                'id' => new Link(fromClass: ForecastClosure::class),
            ],
            defaults: ['parentProperty' => 'forecastClosure', 'class' => ForecastClosureFile::class],
            controller: DeleteController::class,
            security: "is_granted('FORECAST_CLOSURE_WRITE_VOTER', object)",
            name: 'delete_forecast_closure_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/forecast_closures/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'forecastClosureFiles', fromClass: ForecastClosureFile::class),
                'id' => new Link(fromClass: ForecastClosure::class),
            ],
            defaults: ['parentProperty' => 'forecastClosure', 'class' => ForecastClosureFile::class],
            controller: DownloadController::class,
            name: 'download_forecast_closure_file'
        ),
        new Post(
            denormalizationContext: ['groups' => ['forecast_closure_write']],
            securityPostDenormalize: "is_granted('FORECAST_CLOSURE_WRITE_VOTER', object)",
        ),
        new Post(
            uriTemplate: '/forecast_closures/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getForecastClosureFiles', 'class' => ForecastClosureFile::class],
            controller: UploadController::class,
            security: "is_granted('FORECAST_CLOSURE_WRITE_VOTER', object)",
            deserialize: false,
            name: 'upload_forecast_closure_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['forecast_closure_detail', 'expose_legacy', 'people_public', 'file', 'competitor_public', 'sales_forecast_public', 'location_public', 'customer_public', 'catalogue_public', 'country', 'network', 'currency']],
)]
#[ORM\Table(name: 'forecast_closures')]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'createdAt',
    'salesForecast.asm.lastname',
    'salesForecast.id',
    'salesForecast.sso.name',
    'salesForecast.product.name',
    'salesForecast.factory.name',
    'salesForecast.buyer.name',
    'salesForecast.endUser.name',
    'salesForecast.quantity',
    'competitor.name',
    'orderedQuantity',
    'salesForecast.estimatedSaleDate',
    'status',
    'reason',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact', 'salesForecast.estimatedSaleDate' => 'exact'])]
#[ApiFilter(ExistsFilter::class, properties: ['competitor'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'salesForecast.id' => 'exact',
    'salesForecast.factory.name' => 'partial',
    'salesForecast.sso.name' => 'partial',
    'salesForecast.endUser.name' => 'partial',
    'salesForecast.buyer.name' => 'partial',
    'salesForecast.product.name' => 'partial',
    'comment' => 'partial',
    'reason' => 'partial',
])]
#[ApiFilter(SearchFilter::class, properties: [
    'status' => 'exact',
    'reason' => 'exact',
    'legacyId' => 'exact',
    'salesForecast' => 'exact',
    'competitor' => 'exact',
    'salesForecast.buyer' => 'exact',
    'salesForecast.endUser' => 'exact',
    'salesForecast.sso' => 'exact',
    'salesForecast.factory' => 'exact',
    'salesForecast.asm' => 'exact',
    'salesForecast.buyer.country' => 'exact',
    'salesForecast.product' => 'exact',
    'salesForecast.product.family.productType' => 'exact',
    'salesForecast.status' => 'exact',
])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'fcr')]
#[Legacy\ExtraColumn(column: 'exwprice', value: 0)]
class ForecastClosure
{
    use LegacyIdentifierTrait;

    final public const string PARTIAL_ORDERED = 'PARTIAL-ORDERED';
    final public const string PARTIAL_LOST = 'PARTIAL-LOST';
    final public const string REASON_PERFORMANCE = 'PERFORMANCE';
    final public const string REASON_SUPPORT = 'SUPPORT';
    final public const string REASON_SALES = 'SALES';
    final public const string REASON_CANCELLED = 'CANCELLED';
    final public const string REASON_PRICE = 'PRICE';
    final public const string REASON_PAYMENT = 'PAYMENT';
    final public const string REASON_LEAD_TIME = 'LEAD_TIME';
    final public const string REASON_LOYALTY = 'LOYALTY';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['forecast_closure', 'sales_forecast_detail', 'forecast_closure_detail'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\SalesForecast', inversedBy: 'forecastClosures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['forecast_closure_write', 'forecast_closure_edit', 'forecast_closure', 'forecast_closure_detail', 'competitor_pricing'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private SalesForecast $salesForecast;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Choice(choices: [SalesForecast::ORDERED, SalesForecast::LOST, self::PARTIAL_ORDERED, self::PARTIAL_LOST])]
    #[Groups(['forecast_closure_write', 'forecast_closure_detail', 'forecast_closure', 'sales_forecast_detail'])]
    #[Legacy\Column(column: 'sub_status')]
    #[Legacy\Column(column: 'sfr_status', transformer: KeyValue::class, options: ['pairs' => [self::PARTIAL_ORDERED => SalesForecast::PARTIAL, self::PARTIAL_LOST => SalesForecast::PARTIAL, SalesForecast::ORDERED => SalesForecast::ORDERED, SalesForecast::LOST => SalesForecast::LOST]])]
    private string $status;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Groups(['forecast_closure_write', 'forecast_closure_edit', 'forecast_closure_detail', 'forecast_closure', 'sales_forecast_detail'])]
    #[Legacy\Column(column: 'ordered_qty')]
    private int $orderedQuantity = 0;

    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Type(type: 'string')]
    #[Groups(['forecast_closure_write', 'forecast_closure_edit', 'forecast_closure_detail', 'forecast_closure', 'sales_forecast_detail'])]
    #[Assert\Choice(choices: [self::REASON_PERFORMANCE, self::REASON_SUPPORT, self::REASON_SALES, self::REASON_CANCELLED, self::REASON_PRICE, self::REASON_PAYMENT, self::REASON_LEAD_TIME, self::REASON_LOYALTY])]
    #[Legacy\Column(column: 'reason', transformer: KeyValue::class, options: ['pairs' => ['LOYALTY' => 'Customer Loyalty', 'LEAD_TIME' => 'Lead-Time', 'PERFORMANCE' => 'Technical / Equipment Performance', 'PRICE' => 'Price', 'SALES' => 'Sales Job', 'SUPPORT' => 'Service & Spare Part Support', 'PAYMENT' => 'Payment Terms', 'CANCELLED' => 'Requirement Cancelled']])]
    private string $reason;

    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Type(type: 'string')]
    #[Groups(['forecast_closure_write', 'forecast_closure_detail'])]
    private string $comment;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['forecast_closure_detail', 'forecast_closure', 'sales_forecast_detail'])]
    #[Legacy\Column(column: 'created', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['forecast_closure_detail', 'forecast_closure', 'sales_forecast_detail'])]
    #[Gedmo\Blameable(on: 'create')]
    private People $poster;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['forecast_closure_write', 'forecast_closure_edit', 'forecast_closure_detail'])]
    #[Legacy\Column(column: 'price')]
    private ?float $price = null;

    #[ORM\Column(name: 'currency', type: 'string', length: 10, nullable: true)]
    private ?string $baanCurrency = null;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[Groups(['forecast_closure_write', 'forecast_closure_edit', 'forecast_closure_detail'])]
    #[Legacy\Column(column: 'currency', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Currency $currency = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Competitor')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['forecast_closure_write', 'forecast_closure_edit', 'forecast_closure_detail', 'forecast_closure', 'sales_forecast_detail'])]
    #[Legacy\Column(column: 'competitor', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Competitor $competitor = null;

    /**
     * @var Collection<CompetitorPricing>
     */
    #[ORM\OneToMany(mappedBy: 'forecastClosure', targetEntity: 'App\Entity\Sales\CompetitorPricing')]
    #[Groups(['sales_forecast_detail'])]
    private Collection $competitorPricings;

    /**
     * @var Collection<ForecastClosureFile>
     */
    #[ORM\OneToMany(mappedBy: 'forecastClosure', targetEntity: 'App\Entity\Sales\ForecastClosureFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['forecast_closure_detail'])]
    private Collection $forecastClosureFiles;

    public function __construct()
    {
        $this->competitorPricings = new ArrayCollection();
        $this->forecastClosureFiles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSalesForecast(): SalesForecast
    {
        return $this->salesForecast;
    }

    public function setSalesForecast(SalesForecast $salesForecast): self
    {
        $this->salesForecast = $salesForecast;

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

    public function getOrderedQuantity(): int
    {
        return $this->orderedQuantity;
    }

    public function setOrderedQuantity(int $orderedQuantity): self
    {
        $this->orderedQuantity = $orderedQuantity;

        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
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

    public function getCompetitor(): ?Competitor
    {
        return $this->competitor;
    }

    public function setCompetitor(?Competitor $competitor): self
    {
        $this->competitor = $competitor;

        return $this;
    }

    /**
     * @return Collection<CompetitorPricing>
     */
    public function getCompetitorPricings(): Collection
    {
        return $this->competitorPricings;
    }

    public function addCompetitorPricing(CompetitorPricing $competitorPricing): self
    {
        $this->competitorPricings->add($competitorPricing);

        return $this;
    }

    public function removeCompetitorPricing(CompetitorPricing $competitorPricing): self
    {
        $this->competitorPricings->removeElement($competitorPricing);

        return $this;
    }

    /**
     * @return Collection<ForecastClosureFile>
     */
    public function getForecastClosureFiles(): Collection
    {
        return $this->forecastClosureFiles;
    }

    public function addForecastClosureFile(ForecastClosureFile $forecastClosureFile): self
    {
        $forecastClosureFile->setForecastClosure($this);
        $this->forecastClosureFiles->add($forecastClosureFile);

        return $this;
    }

    public function removeForecastClosureFile(ForecastClosureFile $forecastClosureFile): self
    {
        $this->forecastClosureFiles->removeElement($forecastClosureFile);

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
}
