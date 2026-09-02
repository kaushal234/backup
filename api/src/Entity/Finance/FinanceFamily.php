<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Entity\Family;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(securityPostDenormalize: "is_granted('FINANCE_FAMILY_CREATE_VOTER')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_FINANCE_FAMILY_ADMIN')"),
        new Delete(security: "is_granted('FEATURE_FINANCE_FAMILY_ADMIN')"),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['family', 'finance_family']],
    denormalizationContext: ['groups' => ['finance_family_write', 'finance_family_pricing_write']],
)]
#[ORM\Table(name: 'finance_families')]
#[ApiFilter(OrderFilter::class, properties: ['name', 'id'])]
#[ApiFilter(BooleanFilter::class)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[App\Loggable]
class FinanceFamily extends Family
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['finance_family', 'finance_family_write', 'finance_family_with_pricings', 'catalogue_product', 'product_export', 'product_list_pricing'])]
    protected string $name;

    /**
     * @var Collection<Location>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\Location')]
    #[MaxDepth(1)]
    #[Assert\All(constraints: new ValidLocation(factory: true))]
    #[Groups(['finance_family', 'finance_family_write'])]
    private Collection $factories;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['finance_family', 'finance_family_write'])]
    private bool $archived = false;

    /**
     * @var Collection<FinanceFamilyPricing>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Finance\FinanceFamilyPricing', mappedBy: 'financeFamily', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['finance_family_write'])]
    private Collection $pricings;

    public function __construct()
    {
        $this->factories = new ArrayCollection();
        $this->pricings = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function addFactory(Location $factory): self
    {
        $this->factories->add($factory);

        return $this;
    }

    public function removeFactory(Location $factory)
    {
        $this->factories->removeElement($factory);

        return $this;
    }

    public function getFactories(): Collection
    {
        return $this->factories;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    public function getPricings(): Collection
    {
        return $this->pricings;
    }

    public function addPricing(FinanceFamilyPricing $pricing): self
    {
        $pricing->setFinanceFamily($this);
        $this->pricings->add($pricing);

        return $this;
    }

    public function removePricing(FinanceFamilyPricing $pricing)
    {
        $this->pricings->removeElement($pricing);

        return $this;
    }
}
