<?php

declare(strict_types=1);

namespace App\Tests\Factory\Support;

use App\Entity\Support\ManualDocument;
use App\Factory\Support\ManualPartFactory;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\ManualItem;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\ManualPart;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterialsItem;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ManualPartFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreationFromCustomizedBillOfMaterials()
    {
        $customizedBillOfMaterialsItemChild = new ManualItem();
        $customizedBillOfMaterialsItemChild->preventive = true;
        $customizedBillOfMaterialsItemChild->maintenance = true;
        $customizedBillOfMaterialsItemChild->overhaul = false;
        $customizedBillOfMaterialsItemChild->critical = false;
        $customizedBillOfMaterialsItemChild->position = 3;
        $customizedBillOfMaterialsItemChild->partNumber = 'BN';
        $customizedBillOfMaterialsItemChild->itemDescription = 'BN Chocolate';
        $customizedBillOfMaterialsItemChild->itemOtherDescription = 'BN Chocolate';
        $customizedBillOfMaterialsItemChild->quantity = 2.2;
        $customizedBillOfMaterialsItemChild->unitOfMeasure = '';

        $customizedBillOfMaterialsItem = new ManualPart();
        $customizedBillOfMaterialsItem->addItem($customizedBillOfMaterialsItemChild);
        $customizedBillOfMaterialsItem->addItem(clone $customizedBillOfMaterialsItemChild);

        $manualPartFactory = new ManualPartFactory();
        $manualDocument = new ManualDocument();

        $manualPartFactory->createCollectionFromCustomizedBillOfMaterialsItem($customizedBillOfMaterialsItem, $manualDocument);

        $this->assertCount(2, $manualDocument->getParts(), 'should contain 2 parts');
        $this->assertTrue($manualDocument->getParts()->first()->preventive);
        $this->assertTrue($manualDocument->getParts()->first()->maintenance);
        $this->assertFalse($manualDocument->getParts()->first()->overhaul);
        $this->assertFalse($manualDocument->getParts()->first()->critical);
    }

    public function testCreationFromBillOfMaterials()
    {
        $customizedBillOfMaterialsItemChild = new CustomizedBillOfMaterialsItem();
        $customizedBillOfMaterialsItemChild->preventive = true;
        $customizedBillOfMaterialsItemChild->maintenance = true;
        $customizedBillOfMaterialsItemChild->overhaul = false;
        $customizedBillOfMaterialsItemChild->critical = false;
        $customizedBillOfMaterialsItemChild->position = 3;
        $customizedBillOfMaterialsItemChild->partNumber = 'BN';
        $customizedBillOfMaterialsItemChild->itemDescription = 'BN Chocolate';
        $customizedBillOfMaterialsItemChild->itemOtherDescription = 'BN Chocolate';
        $customizedBillOfMaterialsItemChild->quantity = 2.2;
        $customizedBillOfMaterialsItemChild->unitOfMeasure = '';

        $customizedBillOfMaterialsItem = new BillOfMaterialItem();
        $customizedBillOfMaterialsItem->addItem($customizedBillOfMaterialsItemChild);
        $customizedBillOfMaterialsItem->addItem(clone $customizedBillOfMaterialsItemChild);

        $manualPartFactory = new ManualPartFactory();
        $manualDocument = new ManualDocument();

        $manualPartFactory->createCollectionFromBillOfMaterialsItem($customizedBillOfMaterialsItem, $manualDocument);

        $this->assertCount(2, $manualDocument->getParts(), 'should contain 2 parts');
        $this->assertTrue($manualDocument->getParts()->first()->preventive);
        $this->assertTrue($manualDocument->getParts()->first()->maintenance);
        $this->assertFalse($manualDocument->getParts()->first()->overhaul);
        $this->assertFalse($manualDocument->getParts()->first()->critical);
    }
}
