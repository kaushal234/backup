<?php

declare(strict_types=1);

namespace Shared\Ressources\Manufacturing\JobShop;

class BenchmarkItem
{
    public string $partNumber;
    public string $itemDescription;
    public int $level;
    public int $position;
    public string $supplySource;
    public float $quantity;

    /** @var PurchasingBySites[]  */
    public array $purchasingBySites = [];

    /** @var BenchmarkItem[] */
    public array $items = [];

    public function setPurchasingBySites(array $purchasingBySites)
    {
        $this->purchasingBySites = $purchasingBySites;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getPartNumber()
    {
        return $this->partNumber;
    }
}