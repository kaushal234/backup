<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\ProductFamily;
use App\Sdk\Resource\ProductType;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Type\Exception\AssertException;

class ProductFamilyResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return ProductFamily::class === $resource && ProductFamily::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): ProductFamily
    {
        try {
            $structure = ProductFamily::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a product family structure.', previous: $e);
        }

        return new ProductFamily(
            iri: $structure['@id'],
            id: $structure['id'],
            name: $structure['name'],
            productType: new ProductType(
                iri: $structure['productType']['@id'],
                id: $structure['productType']['id'],
                englishName: $structure['productType']['englishName'],
                frenchName: $structure['productType']['frenchName'],
                chineseName: $structure['productType']['chineseName'],
            ),
            englishDescription: $structure['englishDescription'],
            frenchDescription: $structure['frenchDescription'],
            chineseDescription: $structure['chineseDescription'],
            dms: $structure['dmsPhoto'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        throw new FailedTransformationException('Collection is not supported for Product Family resource.');
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Pagination is not supported for Product Family resource.');
    }
}
