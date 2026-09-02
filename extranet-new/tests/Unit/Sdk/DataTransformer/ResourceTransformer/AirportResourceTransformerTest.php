<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\AirportResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Airport;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Utils\IriToId;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class AirportResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/airports/1',
            '@type' => 'airport',
            'id' => 42,
            'code' => 'CDG',
            'cityName' => 'Paris',
            'name' => 'Chevalier Du Goûter',
        ]];

        yield [[
            '@id' => '/airports/1',
            '@type' => 'airport',
            'id' => 42,
            'code' => 'CDG',
            'cityName' => null,
            'name' => null,
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'airport',
            'id' => 13,
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
        return Airport::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new AirportResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Airport::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame(IriToId::iriToId($structure['@id']), $resource->id);
        self::assertSame($structure['code'], $resource->code);
        self::assertSame($structure['cityName'] ?? null, $resource->city);
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
