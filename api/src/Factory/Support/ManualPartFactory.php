<?php

declare(strict_types=1);

namespace App\Factory\Support;

use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualPart;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\ShopfloorPdf;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\ShopfloorPdfItem;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\ManualItem;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\ManualPart as ManualPartLn;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterialsItem;

class ManualPartFactory
{
    public function createCollectionFromCustomizedBillOfMaterialsItem(ManualPartLn|CustomizedBillOfMaterialsItem $customizedBillOfMaterialsItem, ManualDocument $manualDocument): void
    {
        foreach ($customizedBillOfMaterialsItem instanceof ManualPartLn ? $customizedBillOfMaterialsItem->getItems() : $customizedBillOfMaterialsItem->getChildren() as $subItem) {
            $manualPart = $this->create($manualDocument, $subItem);
            $manualDocument->addPart($manualPart);
        }
    }

    public function createCollectionFromBillOfMaterialsItem(BillOfMaterialItem $item, ManualDocument $manualDocument): void
    {
        foreach ($item->getItems() as $subItem) {
            $manualPart = $this->create($manualDocument, $subItem);
            $manualDocument->addPart($manualPart);
        }
    }

    public function createCollectionFromBOMShopfloorItem(ShopfloorPdf $item, ManualDocument $manualDocument): void
    {
        foreach ($item->getItems() as $subItem) {
            $manualPart = $this->createForShopfloor($manualDocument, $subItem);
            $manualDocument->addPart($manualPart);
        }
    }

    private function create(ManualDocument $manualDocument, ManualItem|CustomizedBillOfMaterialsItem $item): ManualPart
    {
        $manualPart = new ManualPart();
        $manualPart->document = $manualDocument;
        $manualPart->position = $item->position;
        $manualPart->partNumber = $item->partNumber;
        $manualPart->quantity = $item->quantity;
        $manualPart->unitOfMeasure = $item->unitOfMeasure;
        $manualPart->description = $item->itemDescription;
        $manualPart->otherDescription = $item->itemOtherDescription;
        $manualPart->preventive = $item->preventive;
        $manualPart->maintenance = $item->maintenance;
        $manualPart->overhaul = $item->overhaul;
        $manualPart->critical = $item->critical;

        return $manualPart;
    }

    private function createForShopfloor(ManualDocument $manualDocument, ShopfloorPdfItem $item): ManualPart
    {
        $manualPart = new ManualPart();
        $manualPart->document = $manualDocument;
        $manualPart->position = $item->position;
        $manualPart->partNumber = $item->partNumber;
        $manualPart->quantity = $item->quantity;
        $manualPart->unitOfMeasure = $item->unitOfMeasure;
        $manualPart->description = $item->itemDescription;
        $manualPart->otherDescription = $item->itemOtherDescription;
        $manualPart->preventive = $item->preventive;
        $manualPart->maintenance = $item->maintenance;
        $manualPart->overhaul = $item->overhaul;
        $manualPart->critical = $item->critical;

        return $manualPart;
    }
}
