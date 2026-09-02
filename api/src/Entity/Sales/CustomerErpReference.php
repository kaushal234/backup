<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['customerNumber', 'sso'], message: 'This customer number already exist in this erp.', errorPath: 'customerNumber')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['customer_erp_reference', 'location_public']],
    denormalizationContext: ['groups' => ['customer_erp_reference:write']]
)]
#[ORM\Table(name: 'customer_erp_references')]
#[ORM\UniqueConstraint(name: 'unique_customer_number_per_erp', columns: ['customer_number', 'sso_id'])]
#[ApiFilter(SearchFilter::class, properties: ['customerNumber' => 'exact', 'sso' => 'exact', 'customer' => 'exact'])]
#[App\Loggable(owner: 'customer', ownerRelation: 'customerErpReferences')]
class CustomerErpReference
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['customer_detail', 'customer_erp_reference'])]
    private int $id;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['customer_detail', 'customer_erp_reference:write', 'customer_erp_reference'])]
    private string $customerNumber;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['customer_detail', 'customer_erp_reference:write', 'customer_erp_reference'])]
    #[ValidLocation(sso: true)]
    private Location $sso;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'customerErpReferences')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['customer_erp_reference', 'customer_erp_reference:write'])]
    private Customer $customer;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCustomerNumber(): string
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(string $customerNumber): self
    {
        $this->customerNumber = $customerNumber;

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

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
