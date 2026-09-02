<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Directory;

use App\AI\Factory\Directory\LocationModelFactory;
use App\Entity\Directory\Location;
use LegacyBundle\Entity\Directory\LocationById as LegacyLocation;
use PHPUnit\Framework\TestCase;

final class LocationModelFactoryTest extends TestCase
{
    public function testCreateFromLegacyLocation(): void
    {
        $entity = new LegacyLocation();
        $entity->name = 'PARIS';
        $entity->erp = 42;

        $model = (new LocationModelFactory())->create($entity);

        self::assertSame('PARIS', $model->name);
        self::assertSame(42, $model->erp);
    }

    public function testCreateFromAppLocation(): void
    {
        $entity = $this->createMock(Location::class);
        $entity->method('getName')->willReturn('LYON');
        $entity->method('getErp')->willReturn(7);

        $model = (new LocationModelFactory())->create($entity);

        self::assertSame('LYON', $model->name);
        self::assertSame(7, $model->erp);
    }
}
