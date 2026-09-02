<?php

declare(strict_types=1);

namespace Shared\Models\Manufacturing\JobShop;

class JobShopBillOfMaterialBenchmarkLine
{
    public function __construct(
        public string $partNumber,
        public string $itemDescription,
        public int|null $position,
        public string $supplySource,
        /** @var PurchasingBySiteLines|null */
        public ?PurchasingBySiteLines $mainPurchasing = null,
        /** @var PurchasingBySiteLines[]  */
        public array $purchasingBySites = [],
        public int $level = 0,
        public float|null $quantity = null,
    ) {
    }

    public function setPurchasingBySiteLines(array $purchasingBySites)
    {
        $this->purchasingBySites = $purchasingBySites;
    }
}