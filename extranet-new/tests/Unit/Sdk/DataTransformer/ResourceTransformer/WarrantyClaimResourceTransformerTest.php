<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\WarrantyClaimResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\WarrantyClaim;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class WarrantyClaimResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/warranty_claims/58',
            '@type' => 'WarrantyClaim',
            'status' => 'PENDING',
            'claimDate' => '2021-06-04',
            'type' => 'Loaders',
            'equipmentModel' => '929',
            'serialNumber' => 'T1000',
            'customerName' => 'OOOOOPS',
            'equipmentLocation' => 'ONT',
            'description' => 'test description',
        ]];

        yield [[
            '@id' => '/warranty_claims/59',
            '@type' => 'WarrantyClaim',
            'status' => 'APPROVED',
            'claimDate' => '2023-11-15',
            'type' => '',
            'equipmentModel' => '',
            'serialNumber' => 'Z9999',
            'customerName' => null,
            'equipmentLocation' => null,
            'description' => null,
        ]];

        yield [[
            '@id' => '/warranty_claims/64',
            '@type' => 'WarrantyClaim',
            'status' => 'PENDING',
            'claimDate' => '2024-01-10',
            'type' => 'Loaders',
            'equipmentModel' => '929',
            'serialNumber' => null,
            'customerName' => 'OOOOOPS',
            'equipmentLocation' => 'ONT',
            'equipmentHours' => 1250,
            'claimantDetails' => 'Claimant details',
            'details' => 'Claim details',
            'description' => 'test description',
            'enteredBy' => [
                '@id' => '/users/12',
                '@type' => 'User',
                'username' => 'john.doe',
            ],
        ]];

        yield [[
            '@id' => '/warranty_claims/66',
            '@type' => 'WarrantyClaim',
            'status' => 'PENDING',
            'claimDate' => '2024-01-10',
            'type' => 'Loaders',
            'equipmentModel' => '929',
            'serialNumber' => 'T85401',
            'customerName' => 'OOOOOPS',
            'equipmentLocation' => 'ONT',
            'description' => 'test description',
            'parts' => [
                [
                    'partNumber' => 'WW2006',
                    'partDescription' => 'Willy Waller',
                    'quantity' => '1.00',
                    'unitOfMeasure' => 'EA',
                    'spr' => [
                        '@id' => '/spare_parts_requests/9001',
                        '@type' => 'SparePartsRequest',
                        'id' => 9001,
                        'status' => 'SHIPPED',
                        'createdAt' => '2024-01-30',
                    ],
                ],
                [
                    'partNumber' => 'WW2007',
                    'partDescription' => 'Willy Waller XL',
                    'quantity' => '2.00',
                    'unitOfMeasure' => 'EA',
                    'spr' => null,
                ],
            ],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[]];

        yield [[
            '@id' => '/warranty_claims/60',
            '@type' => 'WarrantyClaim',
            'claimDate' => '2021-06-04',
            'serialNumber' => 'T1000',
        ]];

        yield [[
            '@id' => '/warranty_claims/61',
            '@type' => 'WarrantyClaim',
            'status' => '',
            'claimDate' => '2021-06-04',
            'type' => 'Loaders',
            'equipmentModel' => '929',
            'serialNumber' => 'T1000',
            'customerName' => 'OOOOOPS',
            'equipmentLocation' => 'ONT',
        ]];

        yield [[
            '@id' => '/warranty_claims/62',
            '@type' => 'WarrantyClaim',
            'status' => 'PENDING',
            'claimDate' => null,
            'serialNumber' => 'T1000',
        ]];

        yield [[
            '@id' => '/warranty_claims/63',
            '@type' => 'WarrantyClaim',
            'status' => 'PENDING',
            'claimDate' => '2021-06-04',
            'serialNumber' => null,
        ]];

        yield [[
            '@id' => '/warranty_claims/65',
            '@type' => 'WarrantyClaim',
            'status' => 'PENDING',
            'claimDate' => '2024-01-10',
            'type' => 'Loaders',
            'equipmentModel' => '929',
            'serialNumber' => 'T1000',
            'customerName' => 'OOOOOPS',
            'equipmentLocation' => 'ONT',
            'equipmentHours' => '1250',
        ]];
    }

    protected function getResourceClass(): string
    {
        return WarrantyClaim::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new WarrantyClaimResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(WarrantyClaim::class, $resource);
        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame((int) basename($structure['@id']), $resource->id);
        self::assertSame($structure['status'], $resource->status);
        self::assertSame($structure['claimDate'], $resource->claimDate);
        self::assertSame($structure['type'], $resource->type);
        self::assertSame($structure['equipmentModel'], $resource->equipmentModel);
        self::assertSame($structure['serialNumber'], $resource->serialNumber);
        self::assertSame($structure['customerName'], $resource->customerName);
        self::assertSame($structure['equipmentLocation'], $resource->equipmentLocation);
        self::assertSame($structure['description'], $resource->description);

        if ($fromCollectionOrPage) {
            self::assertNull($resource->equipmentHours);
            self::assertNull($resource->claimantDetails);
            self::assertNull($resource->details);
            self::assertNull($resource->enteredByUsername);
            self::assertSame([], $resource->parts);

            return;
        }

        self::assertSame($structure['equipmentHours'] ?? null, $resource->equipmentHours);
        self::assertSame($structure['claimantDetails'] ?? null, $resource->claimantDetails);
        self::assertSame($structure['details'] ?? null, $resource->details);
        self::assertSame($structure['enteredBy']['username'] ?? null, $resource->enteredByUsername);

        self::assertCount(\count($structure['parts'] ?? []), $resource->parts);

        foreach ($structure['parts'] ?? [] as $index => $expectedPart) {
            $part = $resource->parts[$index];
            self::assertSame($expectedPart['partNumber'], $part->partNumber);
            self::assertSame($expectedPart['partDescription'], $part->partDescription);
            self::assertSame($expectedPart['quantity'], $part->quantity);
            self::assertSame($expectedPart['unitOfMeasure'], $part->unitOfMeasure);
            self::assertSame($expectedPart['spr']['id'] ?? null, $part->sprNumber);
            self::assertSame($expectedPart['spr']['status'] ?? null, $part->sprStatus);
            self::assertSame($expectedPart['spr']['createdAt'] ?? null, $part->sprCreatedAt);
        }
    }

    protected function supportsCollections(): bool
    {
        return false;
    }

    protected function supportsPage(): bool
    {
        return true;
    }
}
