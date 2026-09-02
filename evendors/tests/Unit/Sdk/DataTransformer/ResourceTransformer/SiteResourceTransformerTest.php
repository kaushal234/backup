<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\SiteResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\Site;

/**
 * @extends AbstractResourceTransformerTest<Site>
 *
 * @group unit
 */
final class SiteResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        // #0 nominal case
        yield [[
            '@id' => '/site/1',
            '@type' => 'Site',
            'siteID' => 'foo',
            'siteDescription' => 'foo',
            'siteAddressCode' => 'bar',
            'siteAddressName' => 'baz',
        ]];

        // #0 with an unknwon field
        yield [[
            '@id' => '/site/1',
            '@type' => 'Site',
            'siteID' => 'foo',
            'siteDescription' => 'foo',
            'siteAddressCode' => 'bar',
            'siteAddressName' => 'baz',
            'unknwon_field' => 'qux',
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@id' => '/site/1',
            '@type' => 'Site',
            'siteID' => 1,
            'siteDescription' => 'foo',
            'siteAddressCode' => 'bar',
            'siteAddressName' => [1, 2],
        ]];

        yield [[
            '@id' => '/dms/1',
            '@type' => 'dms',
            'id' => 13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => 'some_type',
            'language' => 'en',
            'portal' => 'some_portal',
            'owner' => [
                '@id' => '/person/3',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
        ]];

        yield [[
            '@id' => '/dms/1',
            '@type' => 'dms',
            'id' => 13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => null,
            'language' => 'en',
            'portal' => '',
            'owner' => [
                '@id' => '/person/3',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
        ]];

        yield [[
            '@id' => '/rfq/1',
            '@type' => 'RequestForQuotation',
            'id' => 13,
            'erp' => 4,
            'buyerEmail' => 'foo@example.com',
            'supplierNumber' => 'XXXX',
            'returnDate' => '23-03-2023',
            'status' => 'Changed',
        ]];

        yield [[
            '@id' => '/rfq/1',
            '@type' => 'RequestForQuotation',
            'id' => 13,
            'erp' => 4,
            'buyerEmail' => null,
            'supplierNumber' => 'XXXX',
            'returnDate' => '23-03-2023',
            'status' => 'New',
        ]];

        yield [[
            '@id' => '/rfq/1',
            '@type' => 'RequestForQuotation',
            'id' => 13,
            'erp' => 43,
            'buyerEmail' => null,
            'supplierNumber' => 'XXXX',
            'returnDate' => '23-03-2023',
            'status' => 'Printed',
        ]];

        yield [[
            '@id' => '/rfq/1',
            '@type' => 'RequestForQuotation',
            'id' => 13,
            'erp' => 43,
            'buyerEmail' => null,
            'supplierNumber' => 'XXXX',
            'returnDate' => '23-03-2023',
            'status' => 'Returned',
        ]];
    }

    protected function getResourceClass(): string
    {
        return Site::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new SiteResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Site::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['siteID'], $resource->siteID);
        self::assertSame($structure['siteDescription'], $resource->siteDescription);
        self::assertSame($structure['siteAddressCode'], $resource->siteAddressCode);
        self::assertSame($structure['siteAddressName'], $resource->siteAddressName);
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
