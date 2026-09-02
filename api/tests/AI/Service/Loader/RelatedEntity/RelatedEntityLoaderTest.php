<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Loader\RelatedEntity;

use App\AI\Service\Loader\RelatedEntity\RelatedEntityLoader;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\Sales\SalesForecast;
use LegacyBundle\Manager\ModLinkManager;
use PHPUnit\Framework\TestCase;

final class RelatedEntityLoaderTest extends TestCase
{
    public function testReturnsEmptyWhenLegacyIdIsNull(): void
    {
        $entity = $this->createMock(SalesForecast::class);
        $entity->method('getLegacyId')->willReturn(null);

        $manager = $this->createMock(ModLinkManager::class);
        $manager->expects(self::never())->method('getFromToLinks');

        $loader = new RelatedEntityLoader($manager);
        self::assertSame([], $loader->findRelatedEntities($entity, 'legacy_id', 'SFR'));
    }

    public function testMapsRowsWithLegacyIdStrategy(): void
    {
        $entity = $this->createMock(SalesForecast::class);
        $entity->method('getLegacyId')->willReturn(123);

        $manager = $this->createMock(ModLinkManager::class);
        $manager
            ->expects(self::once())
            ->method('getFromToLinks')
            ->with(123, 'SFR')
            ->willReturn([
                ['id' => 1, 'parent_id' => 123, 'module' => 'SFR', 'item' => 999, 'type' => 'CRAB'],
                ['id' => 2, 'parent_id' => 123, 'module' => 'SFR', 'item' => 7, 'type' => ''],
                ['id' => 3, 'parent_id' => 555, 'module' => 'CRAB', 'item' => 123, 'type' => 'SFR'],
            ]);

        $loader = new RelatedEntityLoader($manager);
        $result = $loader->findRelatedEntities($entity, 'legacy_id', 'SFR');

        self::assertCount(2, $result);
        self::assertSame('CRAB', $result[0]->type);
        self::assertSame(999, $result[0]->item);
        self::assertSame('CRAB', $result[1]->type);
        self::assertSame(555, $result[1]->item);
    }

    public function testUsesIdStrategyWhenConfigured(): void
    {
        $entity = $this->createMock(TroubleTicket::class);
        $entity->method('getId')->willReturn(456);

        $manager = $this->createMock(ModLinkManager::class);
        $manager
            ->expects(self::once())
            ->method('getFromToLinks')
            ->with(456, 'TTS')
            ->willReturn([]);

        $loader = new RelatedEntityLoader($manager);
        self::assertSame([], $loader->findRelatedEntities($entity, 'id', 'TTS'));
    }
}
