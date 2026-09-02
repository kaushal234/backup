<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion/bill-of-materials',
    normalizationContext: ['groups' => ['bom']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['otherSites'])]
class PurchasingBenchmark
{
    #[ApiProperty(identifier: true)]
    #[Groups(['bom'])]
    public int $site;

    #[ApiProperty(identifier: true)]
    #[Groups(['bom'])]
    public string $project;

    #[ApiProperty(identifier: true)]
    #[Groups(['bom'])]
    public string $product;

    #[Groups(['bom'])]
    public string $itemDescription;

    #[Groups(['bom'])]
    public int $level;

    #[Groups(['bom'])]
    public int $position;

    #[Groups(['bom'])]
    public string $supplySource;

    #[Groups(['bom'])]
    public float $quantity;

    /**
     * @var Collection<PurchasingBenchmarkBySite>
     */
    #[Groups(['bom'])]
    protected Collection $purchasingBySites;

    /**
     * @var Collection<PurchasingBenchmarkItem>
     */
    #[Groups(['bom'])]
    protected Collection $items;

    public function __construct()
    {
        $this->purchasingBySites = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function getPurchasingBySites(): Collection
    {
        return $this->purchasingBySites;
    }

    /**
     * @param ArrayCollection|PurchasingBenchmarkBySite[] $purchasingBySites
     */
    public function setPurchasingBySites(Collection $purchasingBySites): self
    {
        $this->purchasingBySites = $purchasingBySites;

        return $this;
    }

    public function addPurchasingBySite(PurchasingBenchmarkBySite $purchasingBySite): self
    {
        $this->purchasingBySites->add($purchasingBySite);

        return $this;
    }

    public function removePurchasingBySite(PurchasingBenchmarkBySite $purchasingBySite): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection|PurchasingBenchmarkItem[] $items
     */
    public function setItems(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(PurchasingBenchmarkItem $item): self
    {
        $this->items->add($item);

        return $this;
    }

    public function removeItem(PurchasingBenchmarkItem $item): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
