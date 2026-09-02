<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\SupplierResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\Supplier;

/**
 * @extends AbstractResourceTransformerTest<Supplier>
 *
 * @group unit
 */
final class SupplierResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield 'with all data, nominal case' => [[
            '@id' => '/tests/1',
            'name' => 'Dana',
            'code' => 'DAN0013',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield 'no data provided' => [[]];

        yield 'extra field' => [['foo' => 'bar']];

        yield 'not allowed nullable field' => [[
            '@id' => '/tests/3',
            'name' => null,
            'code' => null,
        ]];
    }

    protected function getResourceClass(): string
    {
        return Supplier::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new SupplierResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Supplier::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['name'], $resource->name);
        self::assertSame($structure['code'], $resource->code);
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
