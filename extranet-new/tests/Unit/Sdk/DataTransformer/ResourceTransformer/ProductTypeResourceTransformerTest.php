<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ProductTypeResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\ProductType;
use App\Sdk\Resource\ResourceInterface;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class ProductTypeResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/product_types/1',
            'id' => 1,
            'englishName' => 'name',
            'frenchName' => 'nom',
            'chineseName' => '威廉，你是一根烟斗',
            'dms' => [
                '@id' => '/dms/1',
                '@type' => 'dms',
                'id' => 13,
                'legacyId' => 13,
                'title' => 'some_title',
                'description' => 'some_description',
                'type' => 'some_type',
                'language' => 'en',
                'createdAt' => '2024-01-01',
            ],
            'productFamilies' => [[
                '@id' => '/product_families/1',
                'id' => 1,
                '@type' => 'productFamily',
                'name' => 'family',
                'dmsPhoto' => 123,
            ]],
            'foo' => 'bar',
        ]];

        yield [[
            '@id' => '/product_types/1',
            'id' => 1,
            'englishName' => 'name',
            'frenchName' => null,
            'chineseName' => null,
            'dms' => null,
            'productFamilies' => [],
            'foo' => 'bar',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/product_types/1',
            'id' => 1,
            'englishName' => null,
            'frenchName' => null,
            'chineseName' => null,
            'dms' => null,
            'productFamilies' => [[
                '@id' => '/product_families/1',
                'id' => 1,
                '@type' => 'productFamily',
                'name' => 'family',
                'dmsPhoto' => 123,
            ]],
        ]];

        yield [[
            '@id' => '/product_types/1',
            'englishName' => null,
            'frenchName' => null,
            'chineseName' => null,
            'dms' => null,
            'productFamilies' => [[
                '@id' => '/product_families/1',
                'id' => 1,
                '@type' => 'productFamily',
                'name' => 'family',
                'dmsPhoto' => 123,
            ]],
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
        return ProductType::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ProductTypeResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(ProductType::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['englishName'], $resource->englishName);
        self::assertSame($structure['frenchName'], $resource->frenchName);
        self::assertSame($structure['chineseName'], $resource->chineseName);
        self::assertSame($structure['dms']['@id'] ?? null, $resource->dms?->iri);
        self::assertSame($structure['dms']['id'] ?? null, $resource->dms?->id);
        self::assertSame($structure['dms']['legacyId'] ?? null, $resource->dms?->legacyId);
        self::assertSame($structure['dms']['title'] ?? null, $resource->dms?->title);
        self::assertSame($structure['dms']['type'] ?? null, $resource->dms?->type);
        self::assertSame($structure['dms']['description'] ?? null, $resource->dms?->description);
        self::assertSame($structure['dms']['language'] ?? null, $resource->dms?->language);
        self::assertSame($structure['dms']['createdAt'] ?? null, $resource->dms?->createdAt);
        if (!$fromCollectionOrPage) {
            if (\count($structure['productFamilies'] ?? []) > 0) {
                self::assertSame($structure['productFamilies'][0]['@id'], $resource->productFamilies[0]->iri);
                self::assertSame($structure['productFamilies'][0]['id'], $resource->productFamilies[0]->id);
                self::assertSame($structure['productFamilies'][0]['name'], $resource->productFamilies[0]->name);
                self::assertSame($structure['productFamilies'][0]['dmsPhoto'], $resource->productFamilies[0]->dms);
            }
        }
    }

    protected function supportsCollections(): bool
    {
        return true;
    }

    protected function supportsPage(): bool
    {
        return false;
    }
}
