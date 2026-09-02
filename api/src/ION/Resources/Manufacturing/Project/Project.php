<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\Project;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\JobShop\ProductionOrder;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['project']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['productionOrder', 'languageID'])]
class Project
{
    #[ApiProperty(identifier: true)]
    #[Groups(['project'])]
    public string $site;

    #[ApiProperty(identifier: true)]
    #[Groups(['project'])]
    public string $projectIdentifier;

    #[Groups(['project'])]
    public string $status;

    /**
     * @var ProductionOrder[]
     */
    #[Groups(['project'])]
    private array $productionOrders = [];

    public function getProductionOrders(): array
    {
        return $this->productionOrders;
    }

    public function addProductionOrder(ProductionOrder $productionOrder): self
    {
        $this->productionOrders[] = $productionOrder;

        return $this;
    }

    public function removeProductionOrder(ProductionOrder $productionOrder): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
