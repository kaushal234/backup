<?php

declare(strict_types=1);

namespace App\DataProvider\Purchasing\VendorWarrantyClaim;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimStatisticsDto;
use App\Entity\Directory\Location;
use App\Entity\Finance\ExchangeRate;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use Doctrine\ORM\EntityManagerInterface;

class VendorWarrantyClaimStatisticsDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $context['request'] ?? null;
        $locationId = $request?->query->get('location');
        $location = $locationId ? $this->entityManager->find(Location::class, $locationId) : null;
        $status = $this->entityManager->getRepository(VendorWarrantyClaimStatus::class)->findOneBy(['name' => VendorWarrantyClaimStatus::VENDOR_TO_RESPOND]);

        if (!$location || !$status) {
            return [];
        }

        $vwcResults = $this->entityManager->getRepository(VendorWarrantyClaim::class)->findBy([
            'location' => $location,
            'status' => $status,
        ]);

        $exchangeRates = $this->entityManager->getRepository(ExchangeRate::class)->findBy([], ['applicatedOn' => 'DESC']);

        $exchangeRateMap = [];
        foreach ($exchangeRates as $exchangeRate) {
            $name = $exchangeRate->getCurrency()->getName();
            if (!isset($exchangeRateMap[$name])) {
                $exchangeRateMap[$name] = $exchangeRate->getRate();
            }
        }

        $totalClaimAmount = 0.0;
        foreach ($vwcResults as $vwc) {
            if (isset($vwc->currency) && isset($vwc->requestedCreditAmount)) {
                $exchangeRate = $exchangeRateMap[$vwc->currency->getName()] ?? null;
                if ($exchangeRate) {
                    $totalClaimAmount += $vwc->requestedCreditAmount * $exchangeRate;
                }
            }
        }

        return new VendorWarrantyClaimStatisticsDto(totalClaimAmount: $totalClaimAmount);
    }
}
