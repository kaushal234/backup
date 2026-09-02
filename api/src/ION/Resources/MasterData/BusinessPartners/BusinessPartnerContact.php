<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\BusinessPartners;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\IONFilter;
use App\ION\Resources\Address;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['contact', 'category']],
            provider: CachedIONCollectionDataProvider::class
        ),
        new Get(
            requirements: ['id' => '.*'],
            security: "is_granted('ACCESS_PEOPLE') or (is_granted('ACCESS_VENDOR_USER') and user.getErpIdentifier() === object.contactCode)",
            provider: CachedIONItemDataProvider::class,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['contact', 'contact:item', 'business_partner', 'category', 'address']],
    denormalizationContext: [],
)]
#[ApiFilter(IONFilter::class, properties: ['emailAddress'])]
class BusinessPartnerContact
{
    #[ApiProperty(identifier: true)]
    #[Groups(['contact', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $contactCode;

    #[Groups(['contact'])]
    public string $emailAddress = '';

    #[Groups(['contact'])]
    public string $firstName = '';

    #[Groups(['contact'])]
    public string $middleName = '';

    #[Groups(['contact'])]
    public string $familyName = '';

    #[Groups(['contact'])]
    public string $fullName = '';

    #[Groups(['contact:item'])]
    public string $language = '';

    #[Groups(['contact:item'])]
    public ?Address $address = null;

    #[Groups(['contact'])]
    public string $telephone = '';

    /**
     * @var Collection<BusinessPartner>
     */
    #[Groups(['contact:item', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    private Collection $businessPartners;

    /**
     * @var Collection<BusinessPartnerContactCategory>
     */
    #[Groups(['contact', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    private Collection $categories;

    public function __construct()
    {
        $this->businessPartners = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getBusinessPartners(): Collection
    {
        return $this->businessPartners;
    }

    public function addBusinessPartner(BusinessPartner $businessPartner): self
    {
        $this->businessPartners->add($businessPartner);

        return $this;
    }

    public function removeBusinessPartner(BusinessPartner $businessPartner): self
    {
        $this->businessPartners->removeElement($businessPartner);

        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(BusinessPartnerContactCategory $category): self
    {
        $this->categories->add($category);

        return $this;
    }

    public function removeCategory(BusinessPartnerContactCategory $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function isGrantedCategory(string $requiredCategory): bool
    {
        return !$this->getCategories()->filter(static fn (BusinessPartnerContactCategory $category) => $requiredCategory === $category->code)->isEmpty();
    }

    #[Groups(['contact'])]
    public function isGrantedQualityCategory(): bool
    {
        return $this->isGrantedCategory(BusinessPartnerContactCategory::QUALITY_CATEGORY_NAME);
    }
}
