<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ProductResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Product;
use App\Sdk\Resource\ResourceInterface;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ProductResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/products/1',
            '@type' => 'product',
            'id' => 13,
            'name' => 'Kinder',
        ]];

        yield [[
            '@id' => '/products/1',
            '@type' => 'dms',
            'id' => 13,
            'name' => 'Kinder',
            'foo' => 'bar',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'product',
            'id' => 13,
        ]];

        yield [[
            '@type' => 'foo',
            '@id' => 13,
            'name' => 'Kinder',
        ]];

        yield [[]];

        yield [[
            'username' => 'some_username',
            'email' => 'some_email',
            'firstname' => 'some_firstname',
            'lastname' => 'some_lastname',
        ]];
    }

    protected function getResourceClass(): string
    {
        return Product::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ProductResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Product::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['name'], $resource->name);
    }

    protected function supportsCollections(): bool
    {
        return true;
    }

    protected function supportsPage(): bool
    {
        return true;
    }
}
