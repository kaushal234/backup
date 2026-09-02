<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['material_list']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['operation', 'otherLanguage'])]
class MaterialList
{
    #[ApiProperty(identifier: true)]
    #[Groups(['material_list'])]
    public string $site;

    #[ApiProperty(identifier: true)]
    #[Groups(['material_list'])]
    public string $productionOrder;

    #[Groups(['material_list'])]
    public string $productionOrderStatus;

    #[Groups(['material_list'])]
    public string $project;

    #[Groups(['material_list'])]
    public string $projectStatus;

    /**
     * @var Material[]
     */
    #[Groups(['material_list'])]
    private array $materials = [];

    public function getMaterials(): array
    {
        return $this->materials;
    }

    public function addMaterial(Material $material): self
    {
        $this->materials[] = $material;

        return $this;
    }

    public function removeMaterial(Material $material): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
