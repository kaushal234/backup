<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\RequestForQuotationResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\RequestForQuotation;
use App\Sdk\Resource\RequestForQuotationLine;
use App\Sdk\Resource\ResourceInterface;

use function count;

/**
 * @group unit
 */
final class RequestForQuotationResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield 'Basic structure' => [[
            '@id' => '/rfq/1',
            '@type' => 'RequestForQuotation',
            'requestForQuotationCode' => 'Code13',
            'buyerEmail' => 'foo@example.com',
            'responseDate' => '23-03-2023',
            'status' => 'created',
            'businessPartnerCodes' => ['A', 'B'],
            'site' => [
                '@id' => '/site/1',
                '@type' => 'Site',
                'siteID' => 'site_1',
                'siteDescription' => 'Site 1',
            ],
        ]];

        yield 'Buyer email null' => [[
            '@id' => '/rfq/1',
            '@type' => 'RequestForQuotation',
            'requestForQuotationCode' => 'Code13',
            'buyerEmail' => null,
            'responseDate' => '23-03-2023',
            'status' => 'in.process',
            'businessPartnerCodes' => [],
            'site' => [
                '@id' => '/site/1',
                '@type' => 'Site',
                'siteID' => 'site_1',
                'siteDescription' => 'Site 1',
            ],
        ]];

        yield 'Status sent' => [[
            '@id' => '/rfq/1',
            '@type' => 'RequestForQuotation',
            'requestForQuotationCode' => 'Code13',
            'buyerEmail' => null,
            'responseDate' => '23-03-2023',
            'status' => 'sent',
            'businessPartnerCodes' => [],
            'site' => [
                '@id' => '/site/1',
                '@type' => 'Site',
                'siteID' => 'site_1',
                'siteDescription' => 'Site 1',
            ],
        ]];

        yield 'Status processed' => [[
            '@id' => '/rfq/1',
            '@type' => 'RequestForQuotation',
            'requestForQuotationCode' => 'Code13',
            'buyerEmail' => null,
            'responseDate' => '23-03-2023',
            'status' => 'processed',
            'businessPartnerCodes' => [],
            'site' => [
                '@id' => '/site/1',
                '@type' => 'Site',
                'siteID' => 'site_1',
                'siteDescription' => 'Site 1',
            ],
        ]];

        yield 'Structure with one line and null line site' => [[
            '@id' => '/rfq/2',
            '@type' => 'RequestForQuotation',
            'requestForQuotationCode' => 'Code42',
            'buyerEmail' => 'bar@example.com',
            'responseDate' => '24-03-2023',
            'status' => 'created',
            'businessPartnerCodes' => ['C'],
            'site' => [
                '@id' => '/site/2',
                '@type' => 'Site',
                'siteID' => '2',
                'siteDescription' => 'Site 2',
            ],
            'lines' => [[
                '@id' => '/rfq_lines/1',
                '@type' => 'RequestForQuotationLine',
                'position' => 1,
                'sequence' => 10,
                'item' => 'PART-001',
                'itemDescription' => 'Part 001',
                'date' => '24-03-2023',
                'quantity' => 5,
                'unitOfMeasure' => 'EA',
                'receiptDate' => '30-03-2023',
                'itemRevision' => 'A',
                'site' => null,
            ]],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'dms',
            'id' => 13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => 'some_type',
            'language' => 'en',
            'portal' => 'some_portal',
            'owner' => [
                'username' => '',
                'email' => '',
                'firstname' => '',
                'lastname' => '',
            ],
        ]];

        yield [[
            '@type' => 'foo',
            'id' => 13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => null,
            'language' => 'en',
            'portal' => '',
            'owner' => [
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
        ]];

        yield [[
            '@type' => 'dms',
            'id' => -13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => null,
            'language' => 'en',
            'portal' => '',
            'owner' => [
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
        ]];

        yield [[
            '@type' => 'dms',
            'id' => 13,
            'title' => 'some_title',
            'subject' => 'some_subject',
            'description' => 'some_description',
            'type' => null,
            'language' => 'en',
            'portal' => '',
            'owner' => [],
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
        return RequestForQuotation::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new RequestForQuotationResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(RequestForQuotation::class, $resource);
        self::assertSame($structure['requestForQuotationCode'], $resource->id);
        self::assertSame($structure['buyerEmail'], $resource->buyerEmail);
        self::assertSame($structure['responseDate'], $resource->returnDate);
        self::assertSame($structure['status'], $resource->status->value);
        self::assertSame(implode(', ', $structure['businessPartnerCodes']), $resource->supplierNumber);
        self::assertSame((int) $structure['site']['siteID'], $resource->erp);
        if (isset($structure['lines'])) {
            self::assertCount(count($structure['lines']), $resource->lines);

            foreach ($structure['lines'] as $index => $lineStructure) {
                /** @var RequestForQuotationLine $line */
                $line = $resource->lines[$index];

                self::assertSame($lineStructure['@id'], $line->iri);
                self::assertSame($lineStructure['position'], $line->position);
                self::assertSame($lineStructure['item'], $line->partNumber);
                self::assertSame($lineStructure['itemDescription'], $line->description);
                self::assertSame($lineStructure['date'], $line->date);
                self::assertSame($lineStructure['quantity'], $line->quantity);
                self::assertSame($lineStructure['unitOfMeasure'], $line->unitOfMeasure);
                self::assertSame($lineStructure['receiptDate'], $line->requestedDeliveryDate);
                self::assertSame($lineStructure['itemRevision'], $line->revision);
                if (null === $lineStructure['site']) {
                    self::assertNull($line->site);
                } else {
                    self::assertSame($lineStructure['site'], $line->site);
                }
            }
        } else {
            self::assertSame([], $resource->lines);
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
