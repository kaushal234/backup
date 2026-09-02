<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Directory\Location;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('FEATURE_FINANCE_FAMILY_PRICING_READ')"),
        new Get(security: "is_granted('FEATURE_FINANCE_FAMILY_PRICING_READ')"),
        new Delete(security: "is_granted('FEATURE_FINANCE_FAMILY_PRICING_ADMIN')"),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['finance_family_pricing', 'finance_family']],
    denormalizationContext: ['groups' => ['finance_family_pricing_write']],
    forceEager: false,
)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_pricing_per_family_sso_factory', columns: ['finance_family_id', 'sso_id', 'factory_id'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'updatedAt', 'averagePrice', 'averageMargin'])]
#[ApiFilter(SearchFilter::class, properties: ['sso' => 'exact', 'factory' => 'exact', 'financeFamily' => 'exact'])]
#[Loggable]
class FinanceFamilyPricing
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['finance_family_pricing'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\FinanceFamily', inversedBy: 'pricings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['finance_family_pricing', 'finance_family_pricing_write'])]
    private FinanceFamily $financeFamily;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['finance_family_pricing', 'finance_family_pricing_write'])]
    #[ValidLocation(sso: true)]
    private Location $sso;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['finance_family_pricing', 'finance_family_pricing_write'])]
    #[ValidLocation(factory: true)]
    private Location $factory;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['finance_family_pricing', 'finance_family_pricing_write'])]
    private int $averagePrice;

    #[ORM\Column(type: 'float', nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['finance_family_pricing', 'finance_family_pricing_write'])]
    private float $averageMargin;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Groups(['finance_family_pricing', 'finance_family_pricing_write'])]
    #[Gedmo\Timestampable(on: 'update')]
    private \DateTimeInterface $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFinanceFamily(): FinanceFamily
    {
        return $this->financeFamily;
    }

    public function setFinanceFamily(FinanceFamily $financeFamily): self
    {
        $this->financeFamily = $financeFamily;

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

    public function getAveragePrice(): int
    {
        return $this->averagePrice;
    }

    public function setAveragePrice(int $averagePrice): self
    {
        $this->averagePrice = $averagePrice;

        return $this;
    }

    public function getAverageMargin(): float
    {
        return $this->averageMargin;
    }

    public function setAverageMargin(float $averageMargin): self
    {
        $this->averageMargin = $averageMargin;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedat): self
    {
        $this->updatedAt = $updatedat;

        return $this;
    }
}
