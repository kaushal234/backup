<?php

declare(strict_types=1);

namespace App\Tests\Report\Handler\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Support\EquipmentRecord\EstimatedGreenTagQuantityReport;
use App\Report\Handler\Support\EquipmentRecordGreenTagDateHandler;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class EquipmentRecordGreenTagDateHandlerTest extends TestCase
{
    use ProphecyTrait;

    private EquipmentRecordGreenTagDateHandler $handler;

    protected function setUp(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $connection = $this->prophesize(Connection::class);
        $iriConverter = $this->prophesize(IriConverterInterface::class);

        $this->handler = new EquipmentRecordGreenTagDateHandler(
            $entityManager->reveal(),
            $connection->reveal(),
            $iriConverter->reveal()
        );
    }

    public function testHandleReturnsNullOnInvalidArgs(): void
    {
        $this->assertNull($this->handler->handle('InvalidClass', 'month', 'ratio'));
        $this->assertNull($this->handler->handle(EstimatedGreenTagQuantityReport::class, 'invalid.x', 'ratio'));
        $this->assertNull($this->handler->handle(EstimatedGreenTagQuantityReport::class, 'month', 'invalid.y'));
    }
}
