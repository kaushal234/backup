<?php

declare(strict_types=1);

namespace App\Tests\AI\Security\Sales;

use App\AI\Security\Sales\MarketIntelligenceAccessChecker;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class MarketIntelligenceAccessCheckerTest extends TestCase
{
    public function testSupportsOnlyMarketIntelligence(): void
    {
        $checker = new MarketIntelligenceAccessChecker($this->createMock(Security::class));

        self::assertTrue($checker->supports(MarketIntelligence::class));
        self::assertFalse($checker->supports(\stdClass::class));
    }

    public function testIsGrantedDelegatesToVoter(): void
    {
        $entity = $this->createMock(MarketIntelligence::class);

        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('isGranted')
            ->with('MARKET_INTELLIGENCE_VIEW_VOTER', $entity)
            ->willReturn(false);

        $checker = new MarketIntelligenceAccessChecker($security);

        self::assertFalse($checker->isGranted($entity));
    }
}
