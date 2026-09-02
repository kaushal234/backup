<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Product;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;

class ProductResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return Product::class === $resource && Product::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): Product
    {
        try {
            $structure = Product::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a product structure.', previous: $e);
        }

        return new Product(
            iri: $structure['@id'],
            id: $structure['id'],
            name: $structure['name'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return Product::class === $resource && Product::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = Product::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of product structure.', previous: $e);
        }
        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return Product::class === $resource && Product::getCollectionTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = Product::getPageTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Comment structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        $items = Vector::fromArray($members)->map(static function (array $structure) {
            return new Product(
                iri: $structure['@id'],
                id: $structure['id'],
                name: $structure['name'],
            );
        });

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }
}
