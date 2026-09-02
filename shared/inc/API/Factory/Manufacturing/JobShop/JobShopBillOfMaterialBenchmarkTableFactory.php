<?php

declare(strict_types=1);

namespace Shared\Factory\Manufacturing\JobShop;

use Shared\Models\Manufacturing\JobShop\JobShopBillOfMaterialBenchmarkLine;
use Shared\Ressources\Manufacturing\JobShop\BenchmarkItem;
use Shared\Ressources\Manufacturing\JobShop\JobShopBillOfMaterialBenchmark;

class JobShopBillOfMaterialBenchmarkTableFactory
{
    private JobShopBillOfMaterialBenchmarkTablePurchasingFactory $purchasingLineFactory;

    public function __construct()
    {
        $this->purchasingLineFactory = new JobShopBillOfMaterialBenchmarkTablePurchasingFactory();
    }

    public function createFromJobShopBillOfMaterialBenchmark(JobShopBillOfMaterialBenchmark|BenchmarkItem $jsBom, int $mainSite, int $level = 0, array &$items = [])
    {
        $mainPurchasing = null;
        $purchasingBySites = [];

        foreach ($jsBom->purchasingBySites as $purchasingBySite) {
            $purchasingLine = $this->purchasingLineFactory->createFromJobShopBillOfMaterialBenchmark($jsBom, $purchasingBySite, $mainSite);

            if ($mainSite === $purchasingLine->site) {
                $mainPurchasing = $purchasingLine;
                continue;
            }

            $purchasingBySites[] = $purchasingLine;
        }

        usort($purchasingBySites, static function ($a, $b) {
            return ($a->site < $b->site) ? -1 : 1;
        });

        $items[] =  new JobShopBillOfMaterialBenchmarkLine(
            partNumber: $jsBom->getPartNumber(),
            itemDescription: $jsBom->itemDescription,
            position: $jsBom->position,
            supplySource: $jsBom->supplySource,
            mainPurchasing: $mainPurchasing,
            purchasingBySites: $purchasingBySites,
            level: $level,
            quantity: $jsBom->quantity,
        );

        // Do same work for children
        foreach ($jsBom->items as $item) {
            $this->createFromJobShopBillOfMaterialBenchmark($item, $mainSite, $level + 1, $items);
        }

        return $items;
    }
}