<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\EquipmentRecordResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Utils\IriToId;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class EquipmentRecordResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/equipments/1',
            '@type' => 'equipment',
            'id' => 42,
            'legacyId' => 1,
            'serialNumber' => 'T101010',
            'model' => 'TPX',
            'type' => 'Loader',
            'customerSerialNumber' => 'XXXX',
            'location' => 'Paris',
            'endUser' => [
                '@id' => '/customers/1',
                'name' => 'AIR PES',
            ],
            'optionsDescription' => 'foofoo',
            'manuals' => [
                [
                    '@id' => '/manuals/1',
                    'description' => 'barbar',
                    'features' => 'foobar',
                    'createdAt' => '2024-01-01',
                ],
            ],
            'airport' => [
                '@id' => '/airports/1',
                'code' => 'CDG',
                'cityName' => 'Paris',
                'name' => 'Codeur Du Grenier',
                'country' => [
                    '@id' => '/countries/1',
                    'id' => 1,
                    'name' => 'France',
                ],
            ],
            'projectNumber' => 'T44981',
            'salesOrganisationService' => [
                '@id' => '/locations/1',
                'name' => 'test location',
            ],
            'dateShipped' => '2025-06-01',
            'dateCommissioned' => '2025-06-01',
            'emissionRating' => [
                '@id' => '/emission_ratings/1',
                'id' => 1,
                'name' => 'Emission Rating 1',
                'obsolete' => false,
                'legacyId' => 1,
            ],
        ]];

        yield [[
            '@id' => '/equipments/1',
            '@type' => 'equipment',
            'id' => 42,
            'legacyId' => 1,
            'serialNumber' => 'T101010',
            'model' => 'TPX',
            'type' => null,
            'customerSerialNumber' => null,
            'location' => null,
            'endUser' => null,
            'airport' => null,
            'manuals' => [],
            'projectNumber' => null,
            'salesOrganisationService' => [
                '@id' => '/locations/1',
                'name' => 'test location',
            ],
            'dateShipped' => null,
            'dateCommissioned' => null,
            'emissionRating' => null,
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'equipment',
            'id' => 13,
        ]];

        yield [[
            '@id' => '/equipments/1',
            '@type' => 'equipment',
            'id' => 42,
            'legacyId' => 1,
            'serialNumber' => null,
            'model' => null,
            'type' => 'Loader',
            'customerSerialNumber' => 'XXXX',
            'location' => 'Paris',
            'endUser' => [
                '@id' => '/customers/1',
                'name' => 'AIR PES',
            ],
            'airport' => [
                '@id' => '/airports/1',
                'code' => 'CDG',
                'cityName' => 'Paris',
            ],
            'dateShipped' => '2025-06-01',
            'dateCommissioned' => '2025-06-01',
            'emissionRating' => [
                '@id' => '/emission_rating/1',
                'id' => 1,
                'name' => 'Emission Rating 1',
                'obsolete' => false,
                'legacyId' => 1,
            ],
        ]];

        yield [[
            '@id' => '/equipments/1',
            '@type' => 'equipment',
            'id' => 42,
            'legacyId' => 1,
            'serialNumber' => 'T101010',
            'model' => 'TPX',
            'type' => 'Loader',
            'customerSerialNumber' => 'XXXX',
            'location' => 'Paris',
            'endUser' => [
                '@id' => '/customers/1',
                'name' => 'AIR PES',
            ],
            'optionsDescription' => 'foofoo',
            'manuals' => [
                ['id' => 12],
            ],
            'airport' => [
                '@id' => '/airports/1',
                'code' => 'BDX',
                'cityName' => 'Bordeaux',
            ],
            'dateShipped' => '2025-06-01',
            'dateCommissioned' => '2025-06-01',
            'emissionRating' => [
                '@id' => '/emission_rating/1',
                'id' => 1,
                'name' => 'Emission Rating 1',
                'obsolete' => false,
                'legacyId' => 1,
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
        return EquipmentRecord::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new EquipmentRecordResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(EquipmentRecord::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['legacyId'], $resource->legacyId);
        self::assertSame($structure['serialNumber'], $resource->serialNumber);
        self::assertSame($structure['model'], $resource->product);
        self::assertSame($structure['type'], $resource->productType);
        self::assertSame($structure['customerSerialNumber'], $resource->customerSerialNumber);
        self::assertSame($structure['location'], $resource->location);
        self::assertSame($structure['optionsDescription'] ?? null, $resource->optionsDescription);
        self::assertSame($structure['projectNumber'] ?? null, $resource->projectNumber);

        self::assertSame($structure['endUser']['@id'] ?? null, $resource->customer?->iri);
        self::assertSame($structure['endUser']['name'] ?? null, $resource->customer?->name);

        self::assertSame($structure['airport']['@id'] ?? null, $resource->airport?->iri);
        self::assertSame($structure['airport']['code'] ?? null, $resource->airport?->code);
        self::assertSame($structure['airport']['cityName'] ?? null, $resource->airport?->city);
        self::assertSame($structure['airport']['name'] ?? null, $resource->airport?->name);
        if (null !== $structure['airport']) {
            self::assertSame(IriToId::iriToId($structure['airport']['@id']), $resource->airport?->id);
        }

        self::assertSame($structure['salesOrganisationService']['@id'] ?? null, $resource->salesOrganisationService?->iri);
        self::assertSame($structure['salesOrganisationService']['name'] ?? null, $resource->salesOrganisationService?->name);

        if (!$fromCollectionOrPage) {
            if (\count($structure['manuals'] ?? []) > 0) {
                self::assertSame($structure['manuals'][0]['@id'], $resource->manuals[0]->iri);
                self::assertSame(IriToId::iriToId($structure['manuals'][0]['@id']), $resource->manuals[0]->id);
                self::assertSame($structure['manuals'][0]['features'], $resource->manuals[0]->features);
                self::assertSame($structure['manuals'][0]['description'], $resource->manuals[0]->description);
                self::assertSame($structure['manuals'][0]['createdAt'], $resource->manuals[0]->createdAt->format('Y-m-d'));
            }

            self::assertSame($structure['dateShipped'] ?? null, $resource->dateShipped?->format('Y-m-d'));
            self::assertSame($structure['dateCommissioned'] ?? null, $resource->dateCommissioned?->format('Y-m-d'));
            self::assertSame($structure['emissionRating']['name'] ?? null, $resource->emissionRating?->name);
        }
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
