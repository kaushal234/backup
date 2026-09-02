<?php

declare(strict_types=1);

namespace App\Tests\Manager\Service;

use App\Entity\EquipmentRecord;
use App\Entity\Service\ServiceActivity;
use App\Manager\Service\BlackCat;
use App\Repository\Service\TechnicianOnCallRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class BlackCatTest extends TestCase
{
    use ProphecyTrait;

    public function testWithoutAnyReferenceDate(): void
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(Argument::any())->shouldNotBeCalled();
        $equipmentRecord = new EquipmentRecord();

        $blackCat = new BlackCat($entityManagerProphecy->reveal());

        self::assertFalse($blackCat->isBlackCat($equipmentRecord));
    }

    public function testWithShippedDateFallbackWhenNoCommissioningDate(): void
    {
        // Fixed date: shipped 2025-09-15, referenceDate = 2025-12-14 (~6 months before 2026-06-23, clearly < 1 year)
        $shippedDate = new \DateTime('2025-09-15');

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateShipped($shippedDate);

        $repositoryMock = $this->createMock(TechnicianOnCallRepository::class);
        $repositoryMock
            ->expects($this->once())
            ->method('countByEquipmentRecordAndCreatedDate')
            ->willReturn(10);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy
            ->getRepository(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($repositoryMock);

        $blackCat = new BlackCat($entityManagerProphecy->reveal());

        // referenceDate = 2025-12-14, 10 TOCs >= 6 minimum, avg 10/6 > 0.83 → true
        self::assertTrue($blackCat->isBlackCat($equipmentRecord));
    }

    /**
     * @dataProvider provideBlackCatTestCases
     */
    public function testIsBlackCat(string $dateInterval, int $numberTechnicianOnCalls, bool $expectedResult): void
    {
        $referenceDate = new \DateTime($dateInterval);

        $equipmentRecord = new EquipmentRecord();
        $equipmentRecord->setDateCommissioned($referenceDate);

        $repositoryMock = $this->createMock(TechnicianOnCallRepository::class);
        $repositoryMock
            ->expects($this->once())
            ->method('countByEquipmentRecordAndCreatedDate')
            ->with(
                $equipmentRecord,
                $this->isInstanceOf(\DateTimeInterface::class),
                [ServiceActivity::TROUBLESHOOTING]
            )
            ->willReturn($numberTechnicianOnCalls);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy
            ->getRepository(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($repositoryMock);

        $blackCat = new BlackCat($entityManagerProphecy->reveal());

        self::assertSame($expectedResult, $blackCat->isBlackCat($equipmentRecord));
    }

    public static function provideBlackCatTestCases(): \Generator
    {
        // < 1 year: minimum 6 TOCs required
        yield 'false with <1y and count 4 (below minimum)' => ['-6 months', 4, false];
        yield 'false with <1y and count 5 (below minimum)' => ['-6 months', 5, false];
        // < 1 year: 6 TOCs over 6 months = 1.0/month > 0.83
        yield 'true with <1y and count 6 and avg above threshold' => ['-6 months', 6, true];
        // < 1 year: 6 TOCs over 11 months = 0.545/month < 0.83
        yield 'false with <1y and count 6 and avg below threshold' => ['-11 months', 6, false];

        // >= 1 year: at least 10 TOCs over last 12 months (>= threshold)
        yield 'false with >1y and count 4' => ['-24 months', 4, false];
        yield 'true with >1y and count 10 (at threshold)' => ['-24 months', 10, true];
        yield 'true with >1y and count 11' => ['-24 months', 11, true];
    }
}
