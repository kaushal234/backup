<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ProductFamilyResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\ProductFamily;
use App\Sdk\Resource\ResourceInterface;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ProductFamilyResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/product_families/1',
            'id' => 1,
            '@type' => 'productFamily',
            'name' => 'family',
            'englishDescription' => 'english description',
            'frenchDescription' => 'description française',
            'chineseDescription' => '迈克尔是最棒的',
            'dmsPhoto' => 123,
            'productType' => [
                '@id' => '/product_types/1',
                'id' => 1,
                'englishName' => 'name',
                'frenchName' => 'nom',
                'chineseName' => '威廉，你是一根烟斗',
            ],
            'foo' => 'bar',
        ]];

        yield [[
            '@id' => '/product_families/1',
            'id' => 1,
            '@type' => 'productFamily',
            'name' => 'family',
            'englishDescription' => 'english description',
            'frenchDescription' => null,
            'chineseDescription' => null,
            'dmsPhoto' => 123,
            'productType' => [
                '@id' => '/product_types/1',
                'id' => 1,
                'englishName' => 'name',
                'frenchName' => null,
                'chineseName' => null,
            ],
            'foo' => 'bar',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/product_families/1',
            'id' => 1,
            '@type' => 'productFamily',
            'name' => 'family',
            'englishDescription' => '',
            'frenchDescription' => 'description française',
            'chineseDescription' => '迈克尔是最棒的',
            'dmsPhoto' => 123,
            'productType' => [
                '@id' => '/product_types/1',
                'id' => 1,
                'englishName' => 'name',
                'frenchName' => 'nom',
                'chineseName' => '威廉，你是一根烟斗',
            ],
        ]];

        yield [[
            '@id' => '/product_families/1',
            'id' => 1,
            '@type' => 'productFamily',
            'name' => '',
            'englishDescription' => 'english description',
            'frenchDescription' => 'description française',
            'chineseDescription' => '迈克尔是最棒的',
            'dmsPhoto' => 123,
            'productType' => [
                '@id' => '/product_types/1',
                'id' => 1,
                'englishName' => 'name',
                'frenchName' => 'nom',
                'chineseName' => '威廉，你是一根烟斗',
            ],
        ]];

        yield [[
            '@id' => '/product_families/1',
            'id' => 1,
            '@type' => 'productFamily',
            'name' => 'family',
            'englishDescription' => 'english description',
            'frenchDescription' => 'description française',
            'chineseDescription' => '迈克尔是最棒的',
            'dmsPhoto' => '123',
            'productType' => [
                '@id' => '/product_types/1',
                'id' => 1,
                'englishName' => 'name',
                'frenchName' => '',
                'chineseName' => '',
            ],
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
        return ProductFamily::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ProductFamilyResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(ProductFamily::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['name'], $resource->name);
        self::assertSame($structure['englishDescription'], $resource->englishDescription);
        self::assertSame($structure['frenchDescription'], $resource->frenchDescription);
        self::assertSame($structure['chineseDescription'], $resource->chineseDescription);
        self::assertSame($structure['dmsPhoto'], $resource->dms);
        self::assertSame($structure['productType']['@id'], $resource->productType->iri);
        self::assertSame($structure['productType']['id'], $resource->productType->id);
        self::assertSame($structure['productType']['englishName'], $resource->productType->englishName);
        self::assertSame($structure['productType']['frenchName'], $resource->productType->frenchName);
        self::assertSame($structure['productType']['chineseName'], $resource->productType->chineseName);
    }

    protected function supportsCollections(): bool
    {
        return false;
    }

    protected function supportsPage(): bool
    {
        return false;
    }
}
