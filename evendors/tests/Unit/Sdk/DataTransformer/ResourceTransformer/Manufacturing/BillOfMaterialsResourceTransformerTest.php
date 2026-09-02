<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\Manufacturing;

use App\Sdk\DataTransformer\ResourceTransformer\Manufacturing\BillOfMaterialsResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Manufacturing\BillOfMaterials;
use App\Sdk\Resource\ResourceInterface;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

use function count;

/**
 * @extends AbstractResourceTransformerTest<BillOfMaterials>
 *
 * @group unit
 */
final class BillOfMaterialsResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield 'Simple bill of materials' => [[
            '@id' => '/bill_of_materials/1',
            '@type' => 'BillOfMaterialItem',
            'site' => 125,
            'product' => 'XYZ',
            'itemDescription' => 'ZYX',
            'unitOfMeasure' => 'SI',
            'engineeringRevision' => 'A',
            'engineeringRevisionEffectiveDate' => '2023-11-25 10:10:10',
            'engineeringRevisionExpiryDate' => '2023-11-25 10:10:10',
            'expired' => true,
            'items' => [],
        ]];

        yield 'Bill of materials with items' => [[
            '@id' => '/bill_of_materials/1',
            '@type' => 'BillOfMaterialItem',
            'site' => 125,
            'product' => 'XYZ',
            'itemDescription' => 'ZYX',
            'unitOfMeasure' => 'SI',
            'engineeringRevision' => 'A',
            'engineeringRevisionEffectiveDate' => '2023-11-25 10:10:10',
            'engineeringRevisionExpiryDate' => '2023-11-25 10:10:10',
            'expired' => false,
            'items' => [[
                '@id' => '/bill_of_materials/2',
                '@type' => 'BillOfMaterialItem',
                'partNumber' => 'ABC',
                'quantity' => 12.2,
                'itemDescription' => 'CBD',
                'unitOfMeasure' => 'SENIOR',
                'engineeringRevision' => 'Z',
                'engineeringRevisionEffectiveDate' => '2022-11-25 10:10:10',
                'engineeringRevisionExpiryDate' => '2022-11-25 10:10:10',
                'expired' => false,
                'level' => 0,
                'children' => [[
                    '@id' => '/bill_of_materials/3',
                    '@type' => 'BillOfMaterialItem',
                    'partNumber' => 'ZUT',
                    'quantity' => 12.2,
                    'itemDescription' => 'TUZ',
                    'unitOfMeasure' => 'JUNIOR',
                    'engineeringRevision' => 'M',
                    'engineeringRevisionEffectiveDate' => '2022-11-25 10:10:10',
                    'engineeringRevisionExpiryDate' => '2022-11-25 10:10:10',
                    'expired' => false,
                    'level' => 1,
                    'children' => [],
                ]],
            ]],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield 'no data provided' => [[
            '@id' => '/bill_of_materials/1',
            '@type' => 'BillOfMaterialItem',
            'site' => '125',
            'product' => 'XYZ',
            'itemDescription' => 'ZYX',
            'unitOfMeasure' => 'SI',
            'engineeringRevision' => 'A',
            'engineeringRevisionEffectiveDate' => '2023-11-25 10:10:10',
            'engineeringRevisionExpiryDate' => '2023-11-25 10:10:10',
            'expired' => false,
            'items' => [],
        ]];
    }

    protected function getResourceClass(): string
    {
        return BillOfMaterials::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new BillOfMaterialsResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(BillOfMaterials::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['site'], $resource->site);
        self::assertSame($structure['product'], $resource->partNumber);
        self::assertSame($structure['itemDescription'], $resource->description);
        self::assertSame($structure['unitOfMeasure'], $resource->unitOfMeasure);
        self::assertSame($structure['engineeringRevision'], $resource->revision);
        self::assertSame($structure['engineeringRevisionEffectiveDate'], $resource->effectiveDate);
        self::assertSame($structure['engineeringRevisionExpiryDate'], $resource->expiryDate);
        self::assertSame($structure['expired'], $resource->expired);

        if (count($structure['items']) > 0) {
            self::assertSame($structure['items'][0]['@id'], $resource->children[0]->iri);
            self::assertSame($structure['site'], $resource->children[0]->site);
            self::assertSame($structure['items'][0]['partNumber'], $resource->children[0]->partNumber);
            self::assertSame($structure['items'][0]['quantity'], $resource->children[0]->quantity);
            self::assertSame($structure['items'][0]['itemDescription'], $resource->children[0]->description);
            self::assertSame($structure['items'][0]['unitOfMeasure'], $resource->children[0]->unitOfMeasure);
            self::assertSame($structure['items'][0]['engineeringRevision'], $resource->children[0]->revision);
            self::assertSame($structure['items'][0]['engineeringRevisionEffectiveDate'], $resource->children[0]->effectiveDate);
            self::assertSame($structure['items'][0]['engineeringRevisionExpiryDate'], $resource->children[0]->expiryDate);
            self::assertSame($structure['items'][0]['expired'], $resource->children[0]->expired);
            self::assertSame($structure['items'][0]['level'], $resource->children[0]->level);
            self::assertCount(1, $resource->children[0]->children);

            self::assertSame($structure['items'][0]['children'][0]['@id'], $resource->children[0]->children[0]->iri);
            self::assertSame($structure['site'], $resource->children[0]->children[0]->site);
            self::assertSame($structure['items'][0]['children'][0]['partNumber'], $resource->children[0]->children[0]->partNumber);
            self::assertSame($structure['items'][0]['children'][0]['quantity'], $resource->children[0]->children[0]->quantity);
            self::assertSame($structure['items'][0]['children'][0]['itemDescription'], $resource->children[0]->children[0]->description);
            self::assertSame($structure['items'][0]['children'][0]['unitOfMeasure'], $resource->children[0]->children[0]->unitOfMeasure);
            self::assertSame($structure['items'][0]['children'][0]['engineeringRevision'], $resource->children[0]->children[0]->revision);
            self::assertSame($structure['items'][0]['children'][0]['engineeringRevisionEffectiveDate'], $resource->children[0]->children[0]->effectiveDate);
            self::assertSame($structure['items'][0]['children'][0]['engineeringRevisionExpiryDate'], $resource->children[0]->children[0]->expiryDate);
            self::assertSame($structure['items'][0]['children'][0]['expired'], $resource->children[0]->children[0]->expired);
            self::assertSame($structure['items'][0]['children'][0]['level'], $resource->children[0]->children[0]->level);
            self::assertCount(0, $resource->children[0]->children[0]->children);
        }
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
