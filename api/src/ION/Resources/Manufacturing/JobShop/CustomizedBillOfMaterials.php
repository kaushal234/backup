<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\ProductConfiguration\ProductVariant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @deprecated
 */
#[ApiResource(
    operations: [
        new Get(
            requirements: ['id' => '.*'],
            openapi: new Operation(
                parameters: [
                    new Parameter(name: 'id', in: 'path', description: 'ID is a composite identifier with site and project', required: true, schema: ['type' => 'string'], example: '/site=510;project=ABCDEFG'),
                    new Parameter(name: 'productSignalCodeFilter', in: 'query', description: 'String of signal code separate by | on the product table (example is for schematics)', required: true, schema: ['type' => 'string'], example: 'ESC|HSC|BSC|FLD'),
                    new Parameter(name: 'productSignalCodeFilterMethod', in: 'query', description: 'Comparaison method (example is for schematics)', required: true, schema: ['type' => 'string'], example: 'Equals'),
                    new Parameter(name: 'productSignalCodeAttribute', in: 'query', description: 'Specify signal code field on the product table', required: true, schema: ['type' => 'string'], example: 'engineeringSignalCode'),
                    new Parameter(name: 'itemsSignalCodeFilter', in: 'query', description: 'String of signal code separate by | on the item table (example is for schematics)', required: true, schema: ['type' => 'string'], example: 'ESC|HSC|BSC|FLD'),
                    new Parameter(name: 'itemsSignalCodeFilterMethod', in: 'query', description: 'Comparaison method (example is for schematics)', required: true, schema: ['type' => 'string'], example: 'Equals'),
                    new Parameter(name: 'itemsSignalCodeAttribute', in: 'query', description: 'Specify signal code field on the item table (example is for schematics)', required: true, schema: ['type' => 'string'], example: 'engineeringSignalCode'),
                    new Parameter(name: 'depth', in: 'query', description: 'Max level of depth on the item tree (example is for schematics)', required: true, schema: ['type' => 'int'], example: '20'),
                    new Parameter(name: 'flatResult', in: 'query', description: 'False : response keep tree hierarchy, True: response order items on one level array', required: true, schema: ['type' => 'boolean'], example: '1'),
                ],
            ),
            security: "is_granted('BILL_OF_MATERIAL_VENDOR_VOTER', object)",
        ),
        new Get(
            uriTemplate: '/extranet_customized_bill_of_materials/{id}',
            requirements: ['id' => '.*'],
            security: "is_granted('ACCESS_EXTRANET_USER') and is_granted('BILL_OF_MATERIAL_EXTRANET_VOTER', object)",
            name: 'get_extranet'
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['cbom', 'ion:text']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['depth', 'date', 'flatResult', 'productSignalCodeFilter', 'productSignalCodeFilterMethod', 'productSignalCodeAttribute', 'itemsSignalCodeFilter', 'itemsSignalCodeFilterMethod', 'itemsSignalCodeAttribute', 'otherLanguage'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => [ProductVariant::NORMALIZATION_GROUP]])]
class CustomizedBillOfMaterials implements BillOfMaterialsIdentifiersInterface
{
    use PartNumberTrait;

    #[ApiProperty(identifier: true)]
    #[Groups(['cbom'])]
    public int $site;
    #[ApiProperty(identifier: true)]
    #[Groups(['cbom'])]
    public string $project;

    #[Groups(['cbom'])]
    public string $product = '';

    #[Groups(['cbom'])]
    public ?BillOfMaterial $billOfMaterials = null;

    /**
     * @var Collection<CustomizedBillOfMaterialsItem>
     */
    #[Groups(['cbom'])]
    protected Collection $items;

    /**
     * @var ProductVariant[]
     */
    #[Groups([ProductVariant::NORMALIZATION_GROUP])]
    private array $productVariants = [];

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return Collection<CustomizedBillOfMaterialsItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Collection<CustomizedBillOfMaterialsItem> $items
     */
    public function setItems(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(CustomizedBillOfMaterialsItem $item): self
    {
        $this->items->add($item);

        return $this;
    }

    public function removeItem(CustomizedBillOfMaterialsItem $item): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }

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
}
