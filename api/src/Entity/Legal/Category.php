<?php

declare(strict_types=1);

namespace App\Entity\Legal;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table]
#[ApiResource(
    operations: [
        new Get(
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['category', 'sub_category']],
        ),
        new Put(
            security: "is_granted('CONTRACT_CATEGORIES_VOTER')",
        ),
    ],
    routePrefix: 'contract',
    normalizationContext: ['groups' => ['category', 'sub_category']],
    denormalizationContext: ['groups' => ['category:write']],
    openapi: true,
)]
#[ApiFilter(SearchFilter::class, properties: ['id', 'name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id', 'name'])]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[LockedValue(value: Category::INSURANCES, propertyPath: 'name')]
#[LockedValue(value: Category::REAL_ESTATE, propertyPath: 'name')]
#[LockedValue(value: Category::MA, propertyPath: 'name')]
#[LockedValue(value: Category::CUSTOMERS, propertyPath: 'name')]
#[LockedValue(value: Category::VENDORS, propertyPath: 'name')]
#[LockedValue(value: Category::BANK, propertyPath: 'name')]
#[LockedValue(value: Category::INTERCO, propertyPath: 'name')]
#[ORM\HasLifecycleCallbacks]
class Category
{
    public const string INSURANCES = 'INSURANCES';
    public const string REAL_ESTATE = 'REAL ESTATE';
    public const string MA = 'M&A';
    public const string CUSTOMERS = 'CUSTOMERS';
    public const string VENDORS = 'VENDORS';
    public const string BANK = 'BANK';
    public const string INTERCO = 'INTERCO';
    public const string IP_IT = 'IP/IT';

    #[ORM\Column(type: 'string')]
    #[Groups(groups: ['category'])]
    public string $name;

    #[ORM\Column(type: 'string')]
    #[Groups(groups: ['category', 'category:write'])]
    #[Assert\NotBlank]
    public string $displayedName;

    #[ORM\OneToMany(targetEntity: SubCategory::class, mappedBy: 'category')]
    #[MaxDepth(maxDepth: 1)]
    private Collection $subCategories;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(groups: ['category'])]
    private int $id;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function initializeDisplayedName(): void
    {
        if (!isset($this->displayedName)) {
            $this->displayedName = $this->name;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(SubCategory $subCategory): self
    {
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories->add($subCategory);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): self
    {
        if ($this->subCategories->contains($subCategory)) {
            $this->subCategories->removeElement($subCategory);
        }

        return $this;
    }
}
