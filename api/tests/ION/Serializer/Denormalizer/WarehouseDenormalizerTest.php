<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Serializer\Denormalizer\Warehousing\WarehouseDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class WarehouseDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testWarehouseIsDenormalized()
    {
        $warehouseDenormalizer = new WarehouseDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $expectedData = [
            'code' => '123',
            'name' => 'On en a gros',
            'reorderPoint' => 1.0,
            'safetyStock' => 4.0,
            'inventoryOnHand' => 5.0,
            'itemSafety' => 0.0,
            'allocated' => 0.0,
            'inventoryOnOrder' => 78.1,
            'purchasePrice' => 12.45,
            'standardCost' => 3.14,
            'lastPurchasePriceDate' => null,
            'includeInEnterprisePlanning' => true,
        ];
        $denormalizerProphecy->denormalize($expectedData, 'type', 'xml', ['WAREHOUSE_DENORMALIZER_ALREADY_CALLED' => true])->shouldBeCalledOnce()->willReturn($expectedData);
        $warehouseDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $data = [
            'code' => '   123    ',
            'name' => '   On en a gros',
            'reorderPoint' => '1',
            'safetyStock' => '4',
            'inventoryOnHand' => '5',
            'inventoryOnOrder' => '78.1',
            'itemSafety' => ' ',
            'allocated' => '',
            'purchasePrice' => '12.45',
            'standardCost' => '3.14',
            'lastPurchasePriceDate' => '',
            'includeInEnterprisePlanning' => '1',
        ];
        $context = [];
        $denormalizedData = $warehouseDenormalizer->denormalize($data, 'type', 'xml', $context);

        self::assertSame($expectedData, $denormalizedData);
    }
}
