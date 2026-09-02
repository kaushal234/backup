<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\ProductConfiguration\ProductVariant;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion/customized-bill-of-materials',
    normalizationContext: ['groups' => ['cbom']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['date', 'otherLanguage'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => [ProductVariant::NORMALIZATION_GROUP]])]
class Variant
{
    #[ApiProperty(identifier: true)]
    #[Groups(['cbom'])]
    public int $site;

    #[ApiProperty(identifier: true)]
    #[Groups(['cbom'])]
    public string $project;

    /**
     * @var ProductVariant[]
     */
    #[Groups([ProductVariant::NORMALIZATION_GROUP])]
    private array $productVariants = [];

    public function getProductVariants(): array
    {
        return $this->productVariants;
    }

    public function addProductVariant(ProductVariant $productVariant): self
    {
        $this->productVariants[] = $productVariant;

        return $this;
    }

    public function removeProductVariant(ProductVariant $productVariant): self
    {
        return $this;
    }
}
