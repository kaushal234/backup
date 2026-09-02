<?php

declare(strict_types=1);

namespace App\AI\Security\Sales;

use App\AI\Security\EntityAccessCheckerInterface;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use Symfony\Bundle\SecurityBundle\Security;

class MarketIntelligenceAccessChecker implements EntityAccessCheckerInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(string $class): bool
    {
        return MarketIntelligence::class === $class;
    }

    public function isGranted(object $entity): bool
    {
        return $this->security->isGranted('MARKET_INTELLIGENCE_VIEW_VOTER', $entity);
    }
}
