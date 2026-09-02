<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\LocationResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Location;
use App\Sdk\Resource\ResourceInterface;

/**
 * @extends AbstractResourceTransformerTest<Location>
 *
 * @group unit
 */
final class LocationResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield 'with all data, nominal case' => [[
            '@id' => '/locations/1',
            '@type' => 'Location',
            'name' => 'TLD EUR',
            'erp' => 500,
            'currency' => [
                '@id' => '/currencies/1',
                '@type' => 'Currency',
                'name' => 'EUR',
                'id' => 1,
            ],
        ]];

        yield 'with some nullable data' => [[
            '@id' => '/locations/1',
            '@type' => 'Location',
            'name' => 'TLD EUR',
            'erp' => null,
            'currency' => null,
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield 'no data provided' => [[]];

        yield 'extra field' => [['foo' => 'bar']];

        yield 'not allowed nullable fields' => [[
            '@id' => '/locations/1',
            '@type' => 'Location',
            'name' => null,
            'erp' => null,
            'currency' => null,
        ]];
    }

    protected function getResourceClass(): string
    {
        return Location::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new LocationResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Location::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['name'], $resource->name);
        self::assertSame($structure['erp'], $resource->erp);

        if (null !== $structure['currency']) {
            self::assertSame($structure['currency']['@id'], $resource->currency->iri);
            self::assertSame($structure['currency']['name'], $resource->currency->name);
            self::assertSame($structure['currency']['id'], $resource->currency->id);
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
