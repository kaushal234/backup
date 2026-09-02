<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\Supplier;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\DataProcessor\Purchasing\SupplierDataProcessor;
use App\Dto\Purchasing\Supplier\SupplierInput;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This resource is populated from Business Partners of ERP LN.
 * We use the business partner code as an identifier of supplier code.
 * It should never be allowed to write for users, only admin (or devs).
 */
#[ORM\Entity]
#[ORM\Table(name: 'suppliers')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            openapi: true,
            security: "is_granted('AUTHORIZED_APPLICATION_FEATURE_WRITE_SUPPLIER')",
            input: SupplierInput::class,
            processor: SupplierDataProcessor::class,
        ),
    ],
    routePrefix: 'purchasing',
    normalizationContext: ['groups' => ['supplier', 'currency', 'people_public', 'location_public', 'expose_legacy', 'country_list']],
    denormalizationContext: ['groups' => ['supplier:write']],
)]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'name' => 'partial',
    'code' => 'partial',
    'location.name' => 'partial',
    'country.name' => 'partial',
    'status' => 'partial',
    'currency.name' => 'partial',
    'buyFrom.buyer.lastname' => 'partial',
    'buyFrom.location.name' => 'partial',
])]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
    'code' => 'partial',
    'location',
    'country',
    'status' => 'exact',
    'currency',
    'buyFrom.buyer',
    'buyFrom.location',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'name',
    'code',
    'location.name',
    'country.name',
    'status',
    'currency.name',
])]
class Supplier
{
    public const string DELETED = 'DELETED';
    public const string ACTIVE = 'ACTIVE';
    public const string INACTIVE = 'INACTIVE';

    #[ORM\Column(type: 'string')]
    #[Groups(['supplier', 'supplier:light', 'supplier:write'])]
    public string $name;

    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['supplier', 'supplier:light', 'supplier:write'])]
    public string $code;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['supplier', 'supplier:write'])]
    public ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[Groups(['supplier', 'supplier:write'])]
    public ?Country $country = null;

    /**
     * If the business partner status in LN is different from ACTIVE, it will send us as default INACTIVE.
     * So, we use the same logic on our side.
     * An LN CLOSED or POTENTIAL status will be set to INACTIVE until the business partner is not ACTIVE.
     */
    #[ORM\Column(type: 'string')]
    #[Groups(['supplier', 'supplier:write'])]
    public string $status = self::INACTIVE;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[Groups(['supplier', 'supplier:write'])]
    public ?Currency $currency = null;

    /**
     * One supplier link to a location is mandatory.
     *
     * @var Collection<SupplierLocation>
     */
    #[ORM\OneToMany(targetEntity: SupplierLocation::class, mappedBy: 'supplier', cascade: ['remove'], orphanRemoval: true)]
    #[Assert\Count(min: 1)]
    #[Groups(['supplier', 'supplier:write'])]
    private Collection $buyFrom;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['supplier'])]
    private int $id;

    public function __construct()
    {
        $this->buyFrom = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<SupplierLocation>
     */
    public function getBuyFrom(): Collection
    {
        return $this->buyFrom;
    }

    public function addBuyFrom(SupplierLocation $buyFrom): self
    {
        if (!$this->buyFrom->contains($buyFrom)) {
            $this->buyFrom->add($buyFrom);
        }

        return $this;
    }

    public function removeBuyFrom(SupplierLocation $buyFrom): self
    {
        if ($this->buyFrom->contains($buyFrom)) {
            $this->buyFrom->removeElement($buyFrom);
        }

        return $this;
    }

    #[Groups(['supplier'])]
    public function getMasterBuyer(): ?People
    {
        foreach ($this->buyFrom as $supplierLocation) {
            if ($supplierLocation->location === $this->location) {
                return $supplierLocation->buyer;
            }
        }

        return null;
    }
}
