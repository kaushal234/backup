<?php

declare(strict_types=1);

namespace App\Entity\Materials\Warehouse;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\Entity\Directory\Location;
use App\Serializer\Filter\ContextFilter;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            controller: NotFoundAction::class,
            output: false,
            read: false,
        ),
    ],
    routePrefix: 'materials/warehouse',
    normalizationContext: ['groups' => ['warehouse:monthly_activities', 'location_public']],
)]
#[ORM\Entity]
#[UniqueEntity(fields: ['location', 'applicatedOn'])]
#[ORM\Table(name: 'warehouse_monthly_activities')]
#[ORM\UniqueConstraint(name: 'unique_activities_perlocation_per_month', columns: ['location_id', 'applicated_on'])]
#[ApiFilter(DateFilter::class, properties: ['applicatedOn'])]
#[ApiFilter(SearchFilter::class, properties: ['location'])]
#[ApiFilter(NumericFilter::class, properties: ['location.erp'])]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['location.name', 'applicatedOn'])]
class MonthlyActivities
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    #[Assert\Type('DateTimeInterface')]
    #[Groups('warehouse:monthly_activities')]
    private ?\DateTimeInterface $applicatedOn = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups('warehouse:monthly_activities')]
    #[ValidLocation(warehouse: true, erpInLN: true)]
    private Location $location;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups('warehouse:monthly_activities')]
    private int $inboundLines = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups('warehouse:monthly_activities')]
    private int $outboundLines = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups('warehouse:monthly_activities')]
    private int $inboundHours = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups('warehouse:monthly_activities')]
    private int $outboundHours = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups('warehouse:monthly_activities')]
    private ?int $improductiveHours = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups('warehouse:monthly_activities')]
    private ?int $excludedHours = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplicatedOn(): ?\DateTimeInterface
    {
        return $this->applicatedOn;
    }

    public function setApplicatedOn(\DateTimeInterface $applicatedOn): self
    {
        $this->applicatedOn = $applicatedOn;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getInboundLines(): ?int
    {
        return $this->inboundLines;
    }

    public function setInboundLines(int $inboundLines): self
    {
        $this->inboundLines = $inboundLines;

        return $this;
    }

    public function getOutboundLines(): ?int
    {
        return $this->outboundLines;
    }

    public function setOutboundLines(int $outboundLines): self
    {
        $this->outboundLines = $outboundLines;

        return $this;
    }

    public function getInboundHours(): ?int
    {
        return $this->inboundHours;
    }

    public function setInboundHours(int $inboundHours): self
    {
        $this->inboundHours = $inboundHours;

        return $this;
    }

    public function getOutboundHours(): ?int
    {
        return $this->outboundHours;
    }

    public function setOutboundHours(int $outboundHours): self
    {
        $this->outboundHours = $outboundHours;

        return $this;
    }

    public function getImproductiveHours(): int
    {
        return $this->improductiveHours ?? 0;
    }

    public function setImproductiveHours(int $improductiveHours): self
    {
        $this->improductiveHours = $improductiveHours;

        return $this;
    }

    public function getExcludedHours(): ?int
    {
        return $this->excludedHours;
    }

    public function setExcludedHours(int $excludedHours): self
    {
        $this->excludedHours = $excludedHours;

        return $this;
    }

    #[Groups('warehouse:monthly_activities')]
    public function getInboundRate(): float
    {
        if (0 === $this->inboundHours) {
            return 0.0;
        }

        return round($this->inboundLines / $this->inboundHours, 1);
    }

    #[Groups('warehouse:monthly_activities')]
    public function getOutboundRate(): float
    {
        if (0 === $this->outboundHours) {
            return 0.0;
        }

        return round($this->outboundLines / $this->outboundHours, 1);
    }

    #[Groups('warehouse:monthly_activities')]
    public function getProductiveRatio(): int
    {
        if (0 === $divisor = $this->inboundHours + $this->outboundHours + $this->getImproductiveHours()) {
            return 0;
        }

        return (int) (($this->inboundHours + $this->outboundHours) * 100 / $divisor);
    }
}
