<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Document;
use App\Sdk\Resource\ProductFamily;
use App\Sdk\Resource\ProductType;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

class ProductTypeResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return ProductType::class === $resource && ProductType::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): ProductType
    {
        try {
            $structure = ProductType::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a product type structure.', previous: $e);
        }

        $document = null !== $structure['dms'] ? new Document(
            iri: $structure['dms']['@id'],
            id: $structure['dms']['id'],
            legacyId: $structure['dms']['legacyId'],
            type: $structure['dms']['type'],
            title: $structure['dms']['title'],
            description: $structure['dms']['description'],
            language: $structure['dms']['language'],
            createdAt: $structure['dms']['createdAt'],
        ) : null;

        return new ProductType(
            iri: $structure['@id'],
            id: $structure['id'],
            englishName: $structure['englishName'],
            frenchName: $structure['frenchName'],
            chineseName: $structure['chineseName'],
            dms: $document,
            productFamilies: Vec\map($structure['productFamilies'] ?? [], static function ($productFamily) {
                return new ProductFamily(
                    iri: $productFamily['@id'],
                    id: $productFamily['id'],
                    name: $productFamily['name'],
                    dms: $productFamily['dmsPhoto'],
                );
            }
            ));
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return ProductType::class === $resource && ProductType::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = ProductType::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of product type structure.', previous: $e);
        }

        $result = $this->getCollectionMembers($collection['hydra:member']);

        return Vector::fromArray($result);
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Pagination is not supported for Product Type resource.');
    }

    /**
     * @param array<string, mixed> $members
     *
     * @return array<ProductType>
     */
    private function getCollectionMembers(array $members): array
    {
        $result = [];
        foreach ($members as $structure) {
            $document = null !== $structure['dms'] ? new Document(
                iri: $structure['dms']['@id'],
                id: $structure['dms']['id'],
                legacyId: $structure['dms']['legacyId'],
                type: $structure['dms']['type'],
                title: $structure['dms']['title'],
                description: $structure['dms']['description'],
                language: $structure['dms']['language'],
                createdAt: $structure['dms']['createdAt'],
            ) : null;

            $result[] = new ProductType(
                iri: $structure['@id'],
                id: $structure['id'],
                englishName: $structure['englishName'],
                frenchName: $structure['frenchName'],
                chineseName: $structure['chineseName'],
                dms: $document,
            );
        }

        return $result;
    }
}
