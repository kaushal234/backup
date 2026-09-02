<?php

declare(strict_types=1);

namespace Shared\Factory\Manufacturing\JobShop;

use Shared\Models\Manufacturing\JobShop\PurchasingBySiteLines;
use Shared\Provider\UserProvider;
use Shared\Ressources\Manufacturing\JobShop\BenchmarkItem;
use Shared\Ressources\Manufacturing\JobShop\JobShopBillOfMaterialBenchmark;
use Shared\Ressources\Manufacturing\JobShop\PurchasingBySites;
use Shared\Ressources\User;
use Symfony\Component\HttpClient\Exception\ClientException;

class JobShopBillOfMaterialBenchmarkTablePurchasingFactory
{
    private array $cachedUser = [];

    private UserProvider $userProvider;

    public function __construct()
    {
        $this->userProvider = new UserProvider();
    }

    public function createFromJobShopBillOfMaterialBenchmark(JobShopBillOfMaterialBenchmark|BenchmarkItem $item, PurchasingBySites $purchasingBySite, int $mainSite)
    {
        $mainPurchasing = $this->getMainPurchasing($item, $mainSite);
        $rate = \tldForex::getRate($purchasingBySite->currency, $mainPurchasing->costCurrency);
        $priceMainCurrency = round($purchasingBySite->price * $rate, 2);
        $standardCostMainCurrency = round($purchasingBySite->standardCost * $rate, 2);
        $gap = $standardCostMainCurrency - $mainPurchasing->standardCost;
        $gapPercent = (0.0 === $mainPurchasing->standardCost)
            ? 0
            : round(($gap / $mainPurchasing->standardCost) * 100, 2);

        return new PurchasingBySiteLines(
            site: $purchasingBySite->site,
            price: $purchasingBySite->price,
            currency: $purchasingBySite->currency,
            leadTime: $purchasingBySite->leadTime,
            mainSupplier: $purchasingBySite->mainSupplier,
            standardCost: $purchasingBySite->standardCost,
            costCurrency: $purchasingBySite->costCurrency,
            priceMainCurrency: $priceMainCurrency,
            standardCostMainCurrency: $standardCostMainCurrency,
            gap: $gap,
            gapPercent: $gapPercent,
            buyer: $this->setUser($purchasingBySite),
        );
    }

    public function setUser(PurchasingBySites $purchasingBySites): ?User
    {
        if (empty($purchasingBySites->buyer)) {
            return null;
        }

        try {
            // Get user fullname
            if (array_key_exists($purchasingBySites->buyer, $this->cachedUser)) {
                return $this->cachedUser[(int)$purchasingBySites->buyer];
            }

            $user = $this->userProvider->findById($purchasingBySites->buyer);
            $this->cachedUser[(int)$purchasingBySites->buyer] = $user;

            return $user;
        } catch (ClientException) {
            return null;
        }
    }

    public function getMainPurchasing(JobShopBillOfMaterialBenchmark|BenchmarkItem $item, int $mainSite): ?PurchasingBySites
    {
        foreach ($item->purchasingBySites as $purchasingBySite) {
            if ($mainSite === $purchasingBySite->site) {
                return $purchasingBySite;
            }
        }

        $description = $item->itemDescription;
        $partNumber = $item->getPartNumber();

        throw new \RuntimeException(sprintf(
            'Item purchasing should have data for selected site. PN: %s, description: %s, site: %d',
            $partNumber,
            $description,
            $mainSite
        ));
    }
}