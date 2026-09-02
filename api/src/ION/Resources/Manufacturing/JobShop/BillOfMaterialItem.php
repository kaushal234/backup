<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Controller\File\DrawingController;
use App\Controller\File\ZipController;
use App\Controller\Manufacturing\BillOfMaterialsPdfController;
use App\Factory\VaultFileDownloadableInterface;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @deprecated
 */
#[ApiResource(
    operations: [
        new Get(
            requirements: ['id' => '.*'],
            security: "is_granted('BILL_OF_MATERIAL_VENDOR_VOTER', object)",
        ),
        new Get(
            uriTemplate: '/bill_of_material_item_zip/{id}',
            formats: ['zip' => 'application/zip'],
            controller: ZipController::class,
            name: 'bill_of_materials_zip',
        ),
        new Get(
            uriTemplate: '/bill_of_material_items_drawing/{id}',
            requirements: ['id' => '.*'],
            controller: DrawingController::class,
            security: "is_granted('BILL_OF_MATERIAL_VENDOR_VOTER', object)",
            name: 'bill_of_materials_drawing',
        ),
        new Get(
            uriTemplate: '/bill_of_material_item_pdf/{id}',
            formats: ['pdf' => 'application/pdf'],
            controller: BillOfMaterialsPdfController::class,
            name: 'bill_of_materials_pdf',
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['cbom']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['item', 'date', 'signalCodeFilter', 'signalCodeFilterMethod', 'signalCodeAttribute', 'otherLanguage'])]
class BillOfMaterialItem extends CustomizedBillOfMaterials implements VaultFileDownloadableInterface
{
    use PartNumberTrait;

    #[ApiProperty(identifier: true)]
    #[Groups(['cbom'])]
    public string $product;

    public function getPartNumber(): string
    {
        return $this->product;
    }

    public function getRevision(): string
    {
        return $this->engineeringRevision;
    }

    public function getSignalCode(): string
    {
        return $this->engineeringSignalCode;
    }

    public function getDrawing(): ?string
    {
        return $this->engineeringRevisionDrawing;
    }
}
