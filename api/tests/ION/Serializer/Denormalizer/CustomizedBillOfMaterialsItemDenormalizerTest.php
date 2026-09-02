<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Denormalizer;

use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterialsItem;
use App\ION\Serializer\Denormalizer\Manufacturing\JobShop\CustomizedBillOfMaterialsItemDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CustomizedBillOfMaterialsItemDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testDenormalizeWithEmptyData()
    {
        $customizedBillOfMaterialsItemDenormalizer = new CustomizedBillOfMaterialsItemDenormalizer();
        $result = $customizedBillOfMaterialsItemDenormalizer->denormalize([], 'type');
        $this->assertNull($result);
        $result = $customizedBillOfMaterialsItemDenormalizer->denormalize('', 'type');
        $this->assertNull($result);
        $result = $customizedBillOfMaterialsItemDenormalizer->denormalize(null, 'type');
        $this->assertNull($result);
        $result = $customizedBillOfMaterialsItemDenormalizer->denormalize(0, 'type');
        $this->assertNull($result);
    }

    /**
     * @dataProvider customizedBillOfMaterialsItemProvider
     */
    public function testDenormalize(CustomizedBillOfMaterialsItem $expected, array $data)
    {
        $customizedBillOfMaterialsItemDenormalizer = new CustomizedBillOfMaterialsItemDenormalizer();
        $item = $customizedBillOfMaterialsItemDenormalizer->denormalize($data, CustomizedBillOfMaterialsItem::class, null, []);

        $this->assertThat($expected, $this->equalTo($item));
    }

    public function testDenormalizeWithChildren()
    {
        $subItem = $this->getBaseCustomizedBillOfMaterialsItem();
        $data = $this->getBaseCustomizedBillOfMaterialsItemDataStructure();
        $subItemData = $this->getBaseCustomizedBillOfMaterialsItemDataStructure();
        $data['children']['customizedBillOfMaterialItem'] = $subItemData;

        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $denormalizerProphecy->denormalize($subItemData, CustomizedBillOfMaterialsItem::class, null, [])->shouldBeCalledOnce()->willReturn($subItem);

        $customizedBillOfMaterialsDenormalizer = new CustomizedBillOfMaterialsItemDenormalizer();
        $customizedBillOfMaterialsDenormalizer->setDenormalizer($denormalizerProphecy->reveal());
        $customizedBillOfMaterialsDenormalizer->denormalize($data, CustomizedBillOfMaterialsItem::class);
    }

    public function customizedBillOfMaterialsItemProvider()
    {
        yield 'With empty data' => [
            $this->getBaseCustomizedBillOfMaterialsItem(),
            $this->getBaseCustomizedBillOfMaterialsItemDataStructure(),
        ];

        $item = new CustomizedBillOfMaterialsItem();
        $item->unitOfMeasure = 'EA';
        $item->itemSignalCode = 'CH0';
        $item->itemDescription = 'Item description';
        $item->itemOtherDescription = 'Item other description';
        $item->itemSelectionCode = 'Item signal code';
        $item->itemType = 'Item type';
        $item->itemGroup = 'Item group';
        $item->customized = true;
        $item->extraInformation = 'Extra information';
        $item->purchaseStatisticsGroup = 'Purchase statistics group';
        $item->buyFromBusinessPartner = 'Buy from business partner';
        $item->buyFromBusinessPartnerName = 'Buy from business partner name';
        $item->buyer = 'buyer';
        $item->supplyTime = 15;
        $item->engineeringRevision = 'A';
        $item->engineeringRevisionEffectiveDate = '2022-02-02';
        $item->engineeringRevisionExpiryDate = '2022-02-02';
        $item->engineeringRevisionDescription = 'Revision desc';
        $item->engineeringRevisionDrawing = 'draw.jpg';
        $item->engineeringSignalCode = 'CH0';
        $item->engineeringDescription = 'Engineering description';
        $item->engineeringOtherDescription = 'Engineering other description';
        $item->engineeringSelectionCode = 'Engineering selection code';
        $item->orderQuantityIncrement = 15;
        $item->minimumOrderQuantity = 15;
        $item->safetyStock = 15;
        $item->warehouse = 'warehouse';
        $item->salesPriceGroup = 'Sales price group';
        $item->estimatedStandardCost = 15.5;
        $item->backflushIfMaterial = true;
        $item->phantom = true;
        $item->signalCodeDescription = 'Chapter 0';
        $item->preventive = true;
        $item->maintenance = false;
        $item->overhaul = true;
        $item->critical = false;
        $item->standardItemProject = 'Standard item project';
        $item->standardItem = 'Standard item      ';
        $item->position = 15;
        $item->partNumberProject = 'Part number project';
        $item->partNumber = 'Part number      ';
        $item->quantity = 15.5;
        $item->productQuantity = 12.5;
        $item->level = 15;
        $item->operation = 'lorem      ';
        $item->customOperation = 'lorem      ';

        yield 'With value' => [
            $item,
            [
                'unitOfMeasure' => 'EA   ',
                'itemSignalCode' => 'CH0        ',
                'itemDescription' => 'Item description      ',
                'itemOtherDescription' => 'Item other description      ',
                'itemSelectionCode' => 'Item signal code      ',
                'itemType' => 'Item type      ',
                'itemGroup' => 'Item group      ',
                'customized' => '1',
                'extraInformation' => 'Extra information      ',
                'purchaseStatisticsGroup' => 'Purchase statistics group      ',
                'buyFromBusinessPartner' => 'Buy from business partner      ',
                'buyFromBusinessPartnerName' => 'Buy from business partner name      ',
                'buyer' => 'buyer      ',
                'supplyTime' => '15',
                'engineeringRevision' => 'A      ',
                'engineeringRevisionEffectiveDate' => '2022-02-02',
                'engineeringRevisionExpiryDate' => '2022-02-02',
                'engineeringRevisionDescription' => 'Revision desc       ',
                'engineeringRevisionDrawing' => 'draw.jpg       ',
                'engineeringSignalCode' => 'CH0      ',
                'engineeringDescription' => 'Engineering description      ',
                'engineeringOtherDescription' => 'Engineering other description      ',
                'engineeringSelectionCode' => 'Engineering selection code      ',
                'orderQuantityIncrement' => '15',
                'minimumOrderQuantity' => '15',
                'safetyStock' => '15',
                'warehouse' => 'warehouse      ',
                'salesPriceGroup' => 'Sales price group      ',
                'estimatedStandardCost' => '15.5',
                'backflushIfMaterial' => '1',
                'phantom' => '1',
                'pmoc' => 'po',
                'standardItemProject' => 'Standard item project      ',
                'standardItem' => 'Standard item      ',
                'position' => '15',
                'partNumberProject' => 'Part number project      ',
                'partNumber' => 'Part number      ',
                'quantity' => '15.5',
                'pbomQuantity' => '12.5',
                'level' => '15',
                'operation' => 'lorem      ',
                'customOperation' => 'lorem      ',
                'children' => [],
            ],
        ];
    }

    private function getBaseCustomizedBillOfMaterialsItemDataStructure(): array
    {
        return [
            'unitOfMeasure' => '',
            'itemSignalCode' => '',
            'itemDescription' => '',
            'itemOtherDescription' => '',
            'itemSelectionCode' => '',
            'itemType' => '',
            'itemGroup' => '',
            'customized' => '',
            'extraInformation' => '',
            'purchaseStatisticsGroup' => '',
            'buyFromBusinessPartner' => '',
            'buyFromBusinessPartnerName' => '',
            'buyer' => '',
            'supplyTime' => '',
            'engineeringRevision' => '',
            'engineeringRevisionEffectiveDate' => '2022-02-02',
            'engineeringRevisionExpiryDate' => '2022-02-02',
            'engineeringRevisionDescription' => '',
            'engineeringRevisionDrawing' => '',
            'engineeringSignalCode' => '',
            'engineeringDescription' => '',
            'engineeringOtherDescription' => '',
            'engineeringSelectionCode' => '',
            'orderQuantityIncrement' => '',
            'minimumOrderQuantity' => '',
            'safetyStock' => '',
            'warehouse' => '',
            'salesPriceGroup' => '',
            'estimatedStandardCost' => '',
            'backflushIfMaterial' => '',
            'phantom' => '',
            'pmoc' => '',
            'standardItemProject' => '',
            'standardItem' => '',
            'position' => '',
            'partNumberProject' => '',
            'partNumber' => '',
            'quantity' => '',
            'pbomQuantity' => '',
            'level' => '',
            'operation' => '',
            'customOperation' => '',
        ];
    }

    private function getBaseCustomizedBillOfMaterialsItem(): CustomizedBillOfMaterialsItem
    {
        $item = new CustomizedBillOfMaterialsItem();

        $item->unitOfMeasure = '';
        $item->itemSignalCode = '';
        $item->itemDescription = '';
        $item->itemOtherDescription = '';
        $item->itemSelectionCode = '';
        $item->itemType = '';
        $item->itemGroup = '';
        $item->customized = false;
        $item->extraInformation = '';
        $item->purchaseStatisticsGroup = '';
        $item->buyFromBusinessPartner = '';
        $item->buyFromBusinessPartnerName = '';
        $item->buyer = '';
        $item->supplyTime = 0;
        $item->engineeringRevision = '';
        $item->engineeringRevisionEffectiveDate = '2022-02-02';
        $item->engineeringRevisionExpiryDate = '2022-02-02';
        $item->engineeringRevisionDescription = '';
        $item->engineeringRevisionDrawing = '';
        $item->engineeringSignalCode = '';
        $item->engineeringDescription = '';
        $item->engineeringOtherDescription = '';
        $item->engineeringSelectionCode = '';
        $item->orderQuantityIncrement = 0;
        $item->minimumOrderQuantity = 0;
        $item->safetyStock = 0;
        $item->warehouse = '';
        $item->salesPriceGroup = null;
        $item->estimatedStandardCost = null;
        $item->backflushIfMaterial = false;
        $item->phantom = false;
        $item->signalCodeDescription = '';
        $item->preventive = false;
        $item->maintenance = false;
        $item->overhaul = false;
        $item->critical = false;
        $item->standardItemProject = '';
        $item->standardItem = '';
        $item->position = 0;
        $item->partNumberProject = '';
        $item->partNumber = '';
        $item->quantity = 0;
        $item->productQuantity = 0;
        $item->level = 0;
        $item->operation = '';
        $item->customOperation = '';

        return $item;
    }
}
