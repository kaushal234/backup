<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\CountryResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Country;
use App\Sdk\Resource\ResourceInterface;

/**
 * @group unit
 */
final class CountryResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/countries/1',
            '@type' => 'country',
            'id' => 13,
            'name' => 'Kinder',
        ]];

        yield [[
            '@id' => '/countries/1',
            '@type' => 'dms',
            'id' => 13,
            'name' => 'Kinder',
            'foo' => 'bar',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'countries',
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
        return Country::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new CountryResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Country::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
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
