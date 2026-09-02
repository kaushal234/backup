<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\Items;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\MasterData\EnterpriseModel\Employee;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['item:list']],
            provider: CachedIONCollectionDataProvider::class
        ),
        new Get(
            requirements: ['id' => '.*'],
            provider: CachedIONItemDataProvider::class,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['item', 'employee', 'business_partner']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE')",
)]
#[ApiFilter(DataAreaFilter::class, properties: ['itemFilter', 'itemDescriptionFilter', 'site', 'itemFilterMethod'])]
class Item
{
    #[ApiProperty(identifier: true)]
    #[Groups(['item', 'item:list'])]
    public string $site;

    #[ApiProperty(identifier: true)]
    #[Groups(['item', 'item:list'])]
    public string $item;

    #[Groups(['item', 'item:list'])]
    public string $project;

    #[Groups(['item'])]
    public ?Employee $buyer = null;

    #[Groups(['item'])]
    public ?BusinessPartner $businessPartner = null;

    #[Groups(['item', 'item:list'])]
    public string $unitOfMeasure;

    #[Groups(['item', 'item:list'])]
    public string $itemDescription;

    #[Groups(['item'])]
    public string $currency;

    #[Groups(['item'])]
    public string $orderLeadTimeInWorkingDays;

    #[Groups(['item', 'item:list'])]
    public string $revision;

    #[Groups(['item'])]
    public string $standardPrice;

    #[Groups(['item'])]
    public string $price;

    #[Groups(['item'])]
    public ?Employee $planner;

    /**
     * @var Collection<BusinessPartner>
     */
    #[Groups(['item'])]
    protected Collection $lastSuppliers;

    public function __construct()
    {
        $this->lastSuppliers = new ArrayCollection();
    }

    public function getLastSuppliers(): Collection
    {
        return $this->lastSuppliers;
    }

    public function setLastSuppliers(Collection $lastSuppliers): self
    {
        $this->lastSuppliers = $lastSuppliers;

        return $this;
    }

    public function addLastSupplier(BusinessPartner $lastSupplier): self
    {
        $this->lastSuppliers->add($lastSupplier);

        return $this;
    }

    public function removeLastSupplier(BusinessPartner $lastSupplier): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
