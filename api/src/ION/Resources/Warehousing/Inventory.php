<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\Warehousing\InventoryWarehousesFilter;
use App\ION\Resources\MasterData\Items\ItemClassification\ProductClass;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            requirements: ['id' => '.*'],
            security: "is_granted('FEATURE_PARTS_DASHBOARD_VIEW')",
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['inventory', 'product:class', 'inventory_file']],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(InventoryWarehousesFilter::class)]
class Inventory
{
    final public const DATAAREA_FILTERS = ['priceBook' => 'SL0000002'];

    #[ApiProperty(identifier: true)]
    #[Groups(['inventory'])]
    public string $project = '';

    #[ApiProperty(identifier: true)]
    #[Groups(['inventory'])]
    public string $item;

    #[Groups(['inventory'])]
    public string $description;

    #[Groups(['inventory'])]
    public string $unitOfMeasure;

    #[Groups(['inventory'])]
    public string $productLine;

    #[Groups(['inventory'])]
    public ?ProductClass $productClass = null;

    #[Groups(['inventory'])]
    public ?float $multiplier;

    #[Groups(['inventory'])]
    public string $pmoc;

    #[Groups(['inventory'])]
    public string $priceBook;

    #[Groups(['inventory'])]
    protected array $siteItems = [];

    #[Groups(['inventory'])]
    protected array $priceBookLines = [];

    #[Groups(['inventory'])]
    protected array $pictures = [];

    public function getSiteItems(): array
    {
        return $this->siteItems;
    }

    public function addSiteItem(SiteItem $siteItem): self
    {
        $this->siteItems[] = $siteItem;

        return $this;
    }

    public function removeSiteItem(SiteItem $siteItem): self
    {
        return $this;
    }

    public function getPriceBookLines(): array
    {
        return $this->priceBookLines;
    }

    public function addPriceBookLine(PriceBookLine $priceBookLine): self
    {
        $this->priceBookLines[] = $priceBookLine;

        return $this;
    }

    public function removePriceBookLine(PriceBookLine $priceBookLine): self
    {
        return $this;
    }

    public function getPictures(): array
    {
        return $this->pictures;
    }

    public function setPictures(array $pictures): self
    {
        $this->pictures = $pictures;

        return $this;
    }

    public function addPicture(InventoryFile $file): self
    {
        $this->pictures[] = $file;

        return $this;
    }
}
