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
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table]
#[ApiResource(
    operations: [
        new Get(
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['sub_category', 'category']],
        ),
        new Put(
            security: "is_granted('CONTRACT_CATEGORIES_VOTER')",
        ),
    ],
    routePrefix: 'contract',
    normalizationContext: ['groups' => ['sub_category', 'category']],
    denormalizationContext: ['groups' => ['sub_category:write']],
    openapi: true,
)]
#[ApiFilter(SearchFilter::class, properties: ['id', 'name', 'category'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id', 'name', 'category'])]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[LockedValue(value: SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY, propertyPath: 'name')]
#[LockedValue(value: SubCategory::CAR_LOCAL_POLICIES, propertyPath: 'name')]
#[LockedValue(value: SubCategory::DO_POLICIES, propertyPath: 'name')]
#[LockedValue(value: SubCategory::GENERAL_LIABILITY, propertyPath: 'name')]
#[LockedValue(value: SubCategory::AERO_LIABILITY, propertyPath: 'name')]
#[LockedValue(value: SubCategory::WORKER_COMPENSATION_LOCAL_POLICIES, propertyPath: 'name')]
#[LockedValue(value: SubCategory::CASH_POOLING_CONTRACT, propertyPath: 'name')]
#[LockedValue(value: SubCategory::MANAGEMENT_FEES_AGREEMENT, propertyPath: 'name')]
#[ORM\HasLifecycleCallbacks]
class SubCategory
{
    public const string BUILDING_PROPERTY_DAMAGES_MASTER_POLICY = 'BUILDING - PROPERTY DAMAGES (master policy)';
    public const string CAR_LOCAL_POLICIES = 'CAR (local policies)';
    public const string DO_POLICIES = 'D&O (master + local policies)';
    public const string GENERAL_LIABILITY = 'GENERAL LIABILITY (master + local)';
    public const string AERO_LIABILITY = 'AERO LIABILITY (master + LOCAL)';
    public const string WORKER_COMPENSATION_LOCAL_POLICIES = 'WORKER COMPENSATION (local policies)';
    public const string CASH_POOLING_CONTRACT = 'CASH POOLING CONTRACT';
    public const string MANAGEMENT_FEES_AGREEMENT = 'MANAGER FEES AGREEMENT';

    public const string EQUOTE_COMMERCIAL_OFFER = 'E-Quote / Commercial Offer and relevant Purchase Order ( PO ) with Terms and Conditions';

    #[ORM\Column(type: 'string')]
    #[Groups(groups: ['sub_category'])]
    public string $name;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'subCategories')]
    #[Groups(groups: ['sub_category'])]
    public Category $category;

    #[ORM\Column(type: 'string')]
    #[Groups(groups: ['sub_category', 'sub_category:write'])]
    #[Assert\NotBlank]
    public string $displayedName;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(groups: ['sub_category'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function initializeDisplayedName(): void
    {
        if (!isset($this->displayedName)) {
            $this->displayedName = $this->name;
        }
    }
}
