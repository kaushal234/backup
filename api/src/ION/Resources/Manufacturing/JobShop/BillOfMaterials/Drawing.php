<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Controller\File\Drawing3DFilesController;
use App\Controller\File\DrawingController;
use App\Controller\File\DrawingExtranetController;
use App\Factory\VaultFileDownloadableInterface;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialsIdentifiersInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/extranet_drawings/{project}/{signalCode}/{id}',
            requirements: ['id' => '.*'],
            controller: DrawingExtranetController::class,
            security: "is_granted('ACCESS_EXTRANET_USER')",
            name: 'get_extranet_drawings',
        ),
        new Get(
            requirements: ['id' => '.*'],
            controller: DrawingController::class,
            security: "is_granted('BILL_OF_MATERIAL_VENDOR_VOTER', object)",
        ),
        new Get(
            uriTemplate: '/drawing_3d_files/{id}',
            requirements: ['id' => '.*'],
            controller: Drawing3DFilesController::class,
            security: "is_granted('ACCESS_PEOPLE') or is_granted('BILL_OF_MATERIAL_VENDOR_VOTER', object)",
            name: 'get_3d_files_drawings',
        ),
    ],
    routePrefix: 'ion/bill-of-materials',
    normalizationContext: ['groups' => ['drawing']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['date'])]
class Drawing implements BillOfMaterialsIdentifiersInterface, VaultFileDownloadableInterface
{
    #[ApiProperty(identifier: true)]
    #[Groups(['drawing'])]
    public int $site;

    #[ApiProperty(identifier: true)]
    #[Groups(['drawing'])]
    public string $project;

    #[ApiProperty(identifier: true)]
    #[Groups(['drawing'])]
    public string $product;

    #[Groups(['drawing'])]
    public string $engineeringRevision;

    #[Groups(['drawing'])]
    public string $engineeringSignalCode;

    #[Groups(['drawing'])]
    public ?string $drawing;

    public function getProject(): string
    {
        return $this->project;
    }

    public function getItem(): string
    {
        return $this->product;
    }

    public function getSite(): int
    {
        return $this->site;
    }

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
        return $this->drawing;
    }
}
