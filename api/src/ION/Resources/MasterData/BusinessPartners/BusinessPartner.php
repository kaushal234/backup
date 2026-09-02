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
use App\ION\Filter\MasterData\BusinessPartners\BusinessPartnerRestrictedFilter;
use App\ION\Filter\MasterData\BusinessPartners\BusinessPartnerSearchFilter;
use App\ION\Filter\MasterData\BusinessPartners\RoleFilter;
use App\ION\Resources\MasterData\EnterpriseModel\Employee;
use App\ION\Resources\MasterData\EnterpriseModel\Entities\Department;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['business_partner']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
            provider: CachedIONCollectionDataProvider::class
        ),
        new Get(
            requirements: ['id' => '.*'],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
            provider: CachedIONItemDataProvider::class,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['business_partner', 'business_partner:detail', 'department', 'employee', 'contact', 'category']],
    denormalizationContext: [],
)]
#[ApiFilter(IONFilter::class, properties: ['name', 'code'])]
#[ApiFilter(RoleFilter::class)]
#[ApiFilter(BusinessPartnerSearchFilter::class)]
#[ApiFilter(BusinessPartnerRestrictedFilter::class)]
class BusinessPartner
{
    /** @var string[] */
    final public const SUPPLIER_ROLES = ['supplier', 'both'];

    #[ApiProperty(identifier: true)]
    #[Groups(['business_partner', 'carrier:detail', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $code;

    #[Groups(['business_partner', 'carrier:detail', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $name;

    #[Groups(['business_partner', 'carrier:detail', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $role;

    #[Groups(['business_partner', 'carrier:detail'])]
    public string $status;

    #[Groups(['business_partner:detail'])]
    public string $currency = '';

    #[Groups(['business_partner:detail', 'carrier:detail'])]
    public string $text = '';

    #[Groups(['business_partner:detail'])]
    public ?Employee $buyer;

    /**
     * @var Collection<BusinessPartnerContact>
     */
    #[Groups(['business_partner:detail'])]
    private Collection $contacts;

    /**
     * @var Collection<Department>
     */
    #[Groups(['business_partner:detail', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    private Collection $buyFromDepartments;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->buyFromDepartments = new ArrayCollection();
    }

    /**
     * @return Collection<BusinessPartnerContact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(BusinessPartnerContact $contact): self
    {
        $this->contacts->add($contact);

        return $this;
    }

    public function removeContact(BusinessPartnerContact $contact): self
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

    /**
     * @return Collection<Department>
     */
    public function getBuyFromDepartments(): Collection
    {
        return $this->buyFromDepartments;
    }

    public function addBuyFromDepartment(Department $department): self
    {
        $this->buyFromDepartments->add($department);

        return $this;
    }

    public function removeBuyFromDepartment(Department $department): self
    {
        $this->buyFromDepartments->removeElement($department);

        return $this;
    }
}
