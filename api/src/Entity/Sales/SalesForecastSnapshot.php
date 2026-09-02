<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EmissionRating;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'sales_forecasts_snapshots')]
#[ORM\Index(columns: ['snapshot_created_at'])]
#[ORM\UniqueConstraint(name: 'unique_subscription_per_resource', columns: ['snapshot_created_at', 'original_sales_forecast_id'])]
class SalesForecastSnapshot
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'date')]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $snapshotCreatedAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\SalesForecast', inversedBy: 'salesForecastSnapshots')]
    private ?SalesForecast $originalSalesForecast = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\MasterSalesForecast', inversedBy: 'salesForecasts')]
    private ?MasterSalesForecast $masterSalesForecast = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastCommentedAt = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $status;

    #[ORM\Column(name: 'last_comment', type: 'text', nullable: true)]
    private ?string $lastComment = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    private Location $sso;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    private Location $factory;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    private People $asm;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    private People $poster;

    #[ORM\Column(name: 'equote_id', type: 'string', nullable: true)]
    private ?string $equoteId = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customer $buyer = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customer $endUser = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customer $thirdParty = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Country')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Common\Airport')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Airport $airport = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Product $product = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quantity;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTimeInterface $estimatedSaleDate;

    #[ORM\Column(type: 'smallint', nullable: false)]
    private int $customerSuccessPercentage;

    #[ORM\Column(type: 'smallint', nullable: false)]
    private int $successPercentage;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EmissionRating')]
    #[ORM\JoinColumn(nullable: true)]
    private ?EmissionRating $tier = null;

    #[ORM\Column(name: 'delinquent', type: 'boolean', nullable: false)]
    private bool $delinquent;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $price = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $margin = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSnapshotCreatedAt(): \DateTimeInterface
    {
        return $this->snapshotCreatedAt;
    }

    public function setSnapshotCreatedAt(\DateTimeInterface $snapshotCreatedAt): self
    {
        $this->snapshotCreatedAt = $snapshotCreatedAt;

        return $this;
    }

    public function getOriginalSalesForecast(): SalesForecast
    {
        return $this->originalSalesForecast;
    }

    public function setOriginalSalesForecast(SalesForecast $originalSalesForecast): self
    {
        $this->originalSalesForecast = $originalSalesForecast;

        return $this;
    }

    public function getMasterSalesForecast(): ?MasterSalesForecast
    {
        return $this->masterSalesForecast;
    }

    public function setMasterSalesForecast(MasterSalesForecast $masterSalesForecast): self
    {
        $this->masterSalesForecast = $masterSalesForecast;

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

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    public function getLastComment(): ?string
    {
        return $this->lastComment;
    }

    public function setLastComment(string $lastComment): self
    {
        $this->lastComment = $lastComment;

        return $this;
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

    public function getPoster(): People
    {
        return $this->poster;
    }

    public function setPoster(People $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getEquoteId(): ?string
    {
        return $this->equoteId;
    }

    public function setEquoteId(?string $equoteId): self
    {
        $this->equoteId = $equoteId;

        return $this;
    }

    public function getBuyer(): ?Customer
    {
        return $this->buyer;
    }

    public function setBuyer(?Customer $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getEndUser(): ?Customer
    {
        return $this->endUser;
    }

    public function setEndUser(?Customer $endUser): self
    {
        $this->endUser = $endUser;

        return $this;
    }

    public function getThirdParty(): ?Customer
    {
        return $this->thirdParty;
    }

    public function setThirdParty(?Customer $thirdParty): self
    {
        $this->thirdParty = $thirdParty;

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getEstimatedSaleDate(): \DateTimeInterface
    {
        return $this->estimatedSaleDate;
    }

    public function setEstimatedSaleDate(\DateTime $estimatedSaleDate): self
    {
        $this->estimatedSaleDate = $estimatedSaleDate;

        return $this;
    }

    public function getCustomerSuccessPercentage(): int
    {
        return $this->customerSuccessPercentage;
    }

    public function setCustomerSuccessPercentage(int $customerSuccessPercentage): self
    {
        $this->customerSuccessPercentage = $customerSuccessPercentage;

        return $this;
    }

    public function getSuccessPercentage(): int
    {
        return $this->successPercentage;
    }

    public function setSuccessPercentage(int $successPercentage): self
    {
        $this->successPercentage = $successPercentage;

        return $this;
    }

    public function getTier(): ?EmissionRating
    {
        return $this->tier;
    }

    public function setTier(?EmissionRating $tier): self
    {
        $this->tier = $tier;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getMargin(): ?float
    {
        return $this->margin;
    }

    public function setMargin(?float $margin): self
    {
        $this->margin = $margin;

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

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getLastCommentedAt(): \DateTimeInterface
    {
        return $this->lastCommentedAt;
    }

    public function setLastCommentedAt(?\DateTime $lastCommentedAt): self
    {
        $this->lastCommentedAt = $lastCommentedAt;

        return $this;
    }
}
