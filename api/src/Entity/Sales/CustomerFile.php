<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\SubDivision;
use App\Entity\File;
use App\Entity\Legal\Contract;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/sales/customer_files/{id}/files',
            uriVariables: [
                'id' => new Link(toProperty: 'customer', fromClass: Customer::class),
            ],
        ),
        new Get(),
    ],
    normalizationContext: ['groups' => ['customer_file', 'file', 'subdivision']],
)]
#[ORM\Entity]
#[ORM\Table(name: 'customers_files')]
#[App\Loggable(owner: 'customer', ownerRelation: 'customerFiles')]
#[ApiFilter(SearchFilter::class, properties: [
    'description' => 'partial',
    'subDivision',
    'subDivision.name' => 'partial',
    'createdAt',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(BooleanFilter::class, properties: ['isContract'])]
class CustomerFile extends File
{
    #[ORM\ManyToOne(targetEntity: SubDivision::class)]
    #[Groups('customer_file')]
    #[ORM\JoinColumn]
    public ?SubDivision $subDivision = null;

    /**
     * Marks this file as a contract, so that submitting it also triggers the creation of a linked Contract.
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups('customer_file')]
    public bool $isContract = false;

    /**
     * The Contract created from this file, once created (kept as a persistent link between the ECUST file and the Legal contract).
     */
    #[ORM\ManyToOne(targetEntity: Contract::class)]
    #[ORM\JoinColumn(name: 'contract_id', nullable: true)]
    #[Groups('customer_file')]
    public ?Contract $contract = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['customer_file'])]
    protected ?string $description = null;
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'customerFiles')]
    #[Groups('customer_file')]
    private ?Customer $customer = null;

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }
}
