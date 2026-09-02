<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Controller\Manufacturing\BOMShopfloorPdfController;
use App\Factory\VaultFileDownloadableInterface;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterial;
use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/shopfloor_pdfs/{id}',
            formats: ['pdf' => 'application/pdf'],
            requirements: ['id' => '.*'],
            controller: BOMShopfloorPdfController::class,
        ),
    ],
    routePrefix: 'ion/bill-of-materials',
    normalizationContext: ['groups' => ['bom', 'cbom', 'ion:engineering:revision', 'ion:pmoc', 'ion:item']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['date', 'otherLanguage'])]
class ShopfloorPdf extends BillOfMaterials implements VaultFileDownloadableInterface
{
    use EngineeringRevisionTrait;

    #[Groups(['bom'])]
    public string $engineeringSignalCode;

    #[Groups(['bom'])]
    public ?string $drawing;

    #[Groups(['bom'])]
    public ?string $signalCodeDescription = null;

    #[Groups(['cbom'])]
    public BillOfMaterial $billOfMaterials;

    /**
     * @var Collection<ShopfloorPdfItem>
     */
    protected Collection $items;

    public function getPartNumber(): string
    {
        return $this->product;
    }

    public function getRevision(): string
    {
        return $this->engineeringRevision;
    }

    public function getSite(): int
    {
        return $this->site;
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
