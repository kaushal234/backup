<?php

declare(strict_types=1);

namespace App\Tests\Formatter\Spreadsheet\Finance;

use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Finance\ManufacturingMargin;
use App\Entity\Manufacturing\ProductManufacturing;
use App\Entity\Sales\Product;
use App\Formatter\Spreadsheet\Finance\ManufacturingMarginSpreadsheetFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use LegacyBundle\Manager\ManufacturingMarginManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ManufacturingMarginSpreadsheetFormatterTest extends TestCase
{
    private ManufacturingMarginSpreadsheetFormatter $formatter;
    private ManufacturingMarginManager&MockObject $manager;
    private RequestStack&MockObject $requestStack;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(ManufacturingMarginManager::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->formatter = new ManufacturingMarginSpreadsheetFormatter($this->manager, $this->requestStack);
    }

    public function testFactoryStandardEfficiencyReturnsNullWhenActualHoursIsZero(): void
    {
        $item = $this->buildItem(actualHours: 0.0, equipmentRecord: null, optionHours: null);
        $this->stubRequest('2026-04-01');
        $this->stubManager($item);

        self::assertNull($this->formatter->computeColumn($item, 'factoryStandardEfficiency'));
    }

    public function testFactoryStandardEfficiencyComputesCorrectlyWhenActualHoursIsNonZero(): void
    {
        $location = $this->createMock(Location::class);

        $productManufacturing = $this->createMock(ProductManufacturing::class);
        $productManufacturing->method('getEffectiveAt')->willReturn(new \DateTime('2026-01-01'));
        $productManufacturing->method('getFactory')->willReturn($location);
        $productManufacturing->method('getIndustrialIncorporationParameter')->willReturn(100);
        $productManufacturing->method('getFactoryStandardEfficiency')->willReturn(100);
        $productManufacturing->method('getModelBaseHours')->willReturn(10);

        $product = $this->createMock(Product::class);
        $product->method('getProductManufacturings')->willReturn(new ArrayCollection([$productManufacturing]));

        $equipmentRecord = $this->createMock(EquipmentRecord::class);
        $equipmentRecord->method('getProduct')->willReturn($product);
        $equipmentRecord->method('getManufacturerLocation')->willReturn($location);

        $item = $this->buildItem(actualHours: 5.0, equipmentRecord: $equipmentRecord, optionHours: 0.0);
        $this->stubRequest('2026-04-01');
        $this->stubManager($item);

        // unitAllocatedHours = round((10 * (100/100)) + 0) = 10
        // factoryStandardEfficiency = round(10 / 5 * 100) = 200
        self::assertSame(200.0, $this->formatter->computeColumn($item, 'factoryStandardEfficiency'));
    }

    private function buildItem(float $actualHours, ?EquipmentRecord $equipmentRecord, ?float $optionHours): ManufacturingMargin
    {
        $item = $this->createMock(ManufacturingMargin::class);
        $item->method('getActualHours')->willReturn($actualHours);
        $item->method('getEquipmentRecord')->willReturn($equipmentRecord);
        $item->method('getOptionConfigurationParameterHours')->willReturn($optionHours);
        $item->method('getFactoryRevenue')->willReturn(0.0);
        $item->method('getActualDirectMarginPercentage')->willReturn(null);
        $item->method('getStandardHours')->willReturn(0.0);
        $item->method('getStandardMaterialCost')->willReturn(0.0);
        $item->method('getActualMaterialCost')->willReturn(0.0);
        $item->method('getStandardOtherDirectCost')->willReturn(0.0);
        $item->method('getActualOtherDirectCost')->willReturn(0.0);

        return $item;
    }

    private function stubRequest(string $after): void
    {
        $request = new Request(['exportedAt' => ['after' => $after]]);
        $this->requestStack->method('getCurrentRequest')->willReturn($request);
    }

    private function stubManager(ManufacturingMargin $item): void
    {
        $this->manager->method('getSalesOrderLineInformation')->with($item)->willReturn([
            'factory_discount' => 0.0,
            'est_dir_margin_per' => null,
            'sol_id' => null,
            'sso_fullname' => null,
            'user_customer' => null,
        ]);
    }
}
