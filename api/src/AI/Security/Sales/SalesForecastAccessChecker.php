<?php

declare(strict_types=1);

namespace App\AI\Security\Sales;

use App\AI\Security\EntityAccessCheckerInterface;
use App\Entity\Sales\SalesForecast;
use Symfony\Bundle\SecurityBundle\Security;

class SalesForecastAccessChecker implements EntityAccessCheckerInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(string $class): bool
    {
        return SalesForecast::class === $class;
    }

    public function isGranted(object $entity): bool
    {
        return $this->security->isGranted('SALES_FORECAST_ACCESS_VOTER', $entity);
    }
}
