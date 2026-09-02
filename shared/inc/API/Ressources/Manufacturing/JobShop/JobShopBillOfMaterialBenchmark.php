<?php

declare(strict_types=1);

namespace Shared\Ressources\Manufacturing\JobShop;

class JobShopBillOfMaterialBenchmark
{
    public int $site;
    public string $project;
    public string $product;
    private string $partNumber;
    public string $itemDescription;
    public string $supplySource;
    public float $standardCost = 0;
    public float $level = 0;

    /** @var PurchasingBySites[]  */
    public array $purchasingBySites = [];

    /** @var BenchmarkItem[] */
    public array $items = [];

    public function getPartNumber(): string
    {
        return $this->product;
    }

    public function setPurchasingBySites(array $purchasingBySites)
    {
        $this->purchasingBySites = $purchasingBySites;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }
}