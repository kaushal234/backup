<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\SupplierCorrectiveActionRequestResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;

use function count;

/**
 * @extends AbstractResourceTransformerTest<SupplierCorrectiveActionRequest>
 *
 * @group unit
 */
final class SupplierCorrectiveActionRequestResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        // #0 with all data
        yield [[
            '@id' => '/suppliercorrectiveactionrequest/1',
            '@type' => 'SupplierCorrectiveActionRequest',
            'id' => 1,
            'description' => 'some_description',
            'shortDescription' => 'some shortDescription',
            'issueOrigin' => 'some issueOrigin',
            'correctiveAction' => 'some correctiveAction',
            'commercialAgreement' => 'some commercialAgreement',
            'verificationDescription' => 'some verificationDescription',
            'preventiveAction' => 'some preventiveAction',
            'conclusion' => 'some conclusion',
            'mainFile' => [
                '@id' => '/suppliercorrectiveactionrequestmainfile/1',
                '@type' => 'SupplierCorrectiveActionRequestMainFile',
                'id' => 1,
                'filePath' => 'some filePath',
                'poster' => [
                    '@id' => '/person/1',
                    'username' => 'some_username',
                    'email' => 'some_email',
                    'firstname' => 'some_firstname',
                    'lastname' => 'some_lastname',
                ],
                'createdAt' => 'some createdAt',
                'description' => 'some description',
                'sha' => 'some sha',
                'mimeType' => 'some mimeType',
                'extension' => 'some extension',
                'size' => 256000,
                'public' => false,
            ],
            'createdAt' => 'some createdAt',
            'approvedAt' => 'some approvedAt',
            'closedAt' => 'some closedAt',
            'poster' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'representative' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'supplierRepresentative' => [
                '@id' => '/vendorusze/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'leader' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'iFactor' => 'some factor',
            'factory' => [
                '@id' => '/locations/1',
                '@type' => 'Location',
                'name' => 'foo',
                'erp' => 314,
                'currency' => [
                    '@id' => '/currency/1',
                    '@type' => 'Currency',
                    'id' => 2,
                    'name' => 'EUR',
                ],
            ],
            'supplierErp' => 1,
            'supplierNumber' => 'some supplierNumber',
            'supplierName' => 'some supplierName',
            'status' => 'PENDING',
            'vendorWarrantyClaims' => [],
            'parts' => [[
                '@id' => '/parts/1',
                '@type' => 'SupplierCorrectiveActionRequestPart',
                'partNumber' => 'some part number',
                'description' => 'some description',
                'quantity' => 1,
                'unitOfMeasure' => 'some unitOfMeasure',
            ]],
            'files' => [[
                '@id' => '/suppliercorrectiveactionrequestfile/1',
                '@type' => 'SupplierCorrectiveActionRequestFile',
                'id' => 1,
                'filePath' => 'some filePath',
                'poster' => [
                    '@id' => '/persons/1',
                    'username' => 'some_username',
                    'email' => 'some_email',
                    'firstname' => 'some_firstname',
                    'lastname' => 'some_lastname',
                ],
                'createdAt' => 'some createdAt',
                'description' => 'some description',
                'sha' => 'some sha',
                'mimeType' => 'some mimeType',
                'extension' => 'some extension',
                'size' => 25000,
                'public' => false,
            ]],
            'activity' => [[
                '@id' => '/activities/1',
                '@type' => 'Comment',
                'resource' => 'some resource',
                'message' => 'some comment',
                'user' => [
                    '@id' => '/persons/1',
                    'username' => 'some_username',
                    'email' => 'some_email',
                    'firstname' => 'some_firstname',
                    'lastname' => 'some_lastname',
                ],
                'createdAt' => 'some created at',
                'updatedAt' => 'some updated at',
                'public' => false,
                'metadata' => [],
                'files' => [],
            ]],
        ]];

        // #1 with nullable values and empty collections
        yield [[
            '@id' => '/suppliercorrectiveactionrequest/1',
            '@type' => 'SupplierCorrectiveActionRequest',
            'id' => 1,
            'description' => 'some_description',
            'shortDescription' => 'some shortDescription',
            'issueOrigin' => 'some issueOrigin',
            'correctiveAction' => 'some correctiveAction',
            'commercialAgreement' => 'some commercialAgreement',
            'verificationDescription' => 'some verificationDescription',
            'preventiveAction' => 'some preventiveAction',
            'conclusion' => 'some conclusion',
            'mainFile' => null,
            'createdAt' => 'some createdAt',
            'approvedAt' => null,
            'closedAt' => null,
            'poster' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'representative' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'supplierRepresentative' => [
                '@id' => '/vendorusze/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'leader' => [
                '@id' => '/persons/1',
                'username' => 'some_username',
                'email' => 'some_email',
                'firstname' => 'some_firstname',
                'lastname' => 'some_lastname',
            ],
            'iFactor' => 'some factor',
            'factory' => [
                '@id' => '/locations/1',
                '@type' => 'Location',
                'name' => 'foo',
                'erp' => 314,
                'currency' => [
                    '@id' => '/currency/1',
                    '@type' => 'Currency',
                    'id' => 2,
                    'name' => 'EUR',
                ],
            ],
            'supplierErp' => 1,
            'supplierNumber' => '',
            'supplierName' => '',
            'status' => 'PENDING',
            'vendorWarrantyClaims' => [],
            'parts' => [],
            'files' => [],
            'activity' => [],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        // #0 no data
        yield [[]];

        // #1 unknwon field only
        yield [[
            'unknwon_field' => 'qux',
        ]];
    }

    protected function getResourceClass(): string
    {
        return SupplierCorrectiveActionRequest::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new SupplierCorrectiveActionRequestResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(SupplierCorrectiveActionRequest::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);

        if (!$fromCollectionOrPage) {
            self::assertSame($structure['description'], $resource->description);
            self::assertSame($structure['shortDescription'], $resource->shortDescription);
            self::assertSame($structure['issueOrigin'], $resource->issueOrigin);
            self::assertSame($structure['correctiveAction'], $resource->correctiveAction);
            self::assertSame($structure['commercialAgreement'], $resource->commercialAgreement);
            self::assertSame($structure['verificationDescription'], $resource->verificationDescription);
            self::assertSame($structure['preventiveAction'], $resource->preventiveAction);
            self::assertSame($structure['conclusion'], $resource->conclusion);

            if (($structure['mainFile'] ?? null) !== null) {
                self::assertSame($structure['mainFile']['@id'], $resource->mainFile->iri);
                self::assertSame($structure['mainFile']['id'], $resource->mainFile->id);
                self::assertSame($structure['mainFile']['filePath'], $resource->mainFile->filePath);
                self::assertSame($structure['mainFile']['createdAt'], $resource->mainFile->createdAt);
                self::assertSame($structure['mainFile']['description'], $resource->mainFile->description);
                self::assertSame($structure['mainFile']['sha'], $resource->mainFile->sha);
                self::assertSame($structure['mainFile']['mimeType'], $resource->mainFile->mimeType);
                self::assertSame($structure['mainFile']['extension'], $resource->mainFile->extension);
                self::assertSame($structure['mainFile']['size'], $resource->mainFile->size);

                self::assertSame($structure['mainFile']['poster']['@id'], $resource->mainFile->poster->iri);
                self::assertSame($structure['mainFile']['poster']['username'], $resource->mainFile->poster->username);
                self::assertSame($structure['mainFile']['poster']['email'], $resource->mainFile->poster->email);
                self::assertSame($structure['mainFile']['poster']['firstname'], $resource->mainFile->poster->firstname);
                self::assertSame($structure['mainFile']['poster']['lastname'], $resource->mainFile->poster->lastname);
            } else {
                self::assertSame($structure['mainFile'], $resource->mainFile);
            }

            self::assertSame($structure['approvedAt'], $resource->approvedAt);
            self::assertSame($structure['closedAt'], $resource->closedAt);

            self::assertSame($structure['representative']['@id'], $resource->representative->iri);
            self::assertSame($structure['representative']['username'], $resource->representative->username);
            self::assertSame($structure['representative']['email'], $resource->representative->email);
            self::assertSame($structure['representative']['firstname'], $resource->representative->firstname);
            self::assertSame($structure['representative']['lastname'], $resource->representative->lastname);

            self::assertSame($structure['supplierRepresentative']['@id'], $resource->supplierRepresentative->iri);
            self::assertSame($structure['supplierRepresentative']['username'], $resource->supplierRepresentative->username);
            self::assertSame($structure['supplierRepresentative']['email'], $resource->supplierRepresentative->email);
            self::assertSame($structure['supplierRepresentative']['firstname'], $resource->supplierRepresentative->firstname);
            self::assertSame($structure['supplierRepresentative']['lastname'], $resource->supplierRepresentative->lastname);

            self::assertSame($structure['vendorWarrantyClaims'], $resource->vendorWarrantyClaims);

            if (count($structure['parts'] ?? []) > 0) {
                self::assertSame($structure['parts'][0]['@id'], $resource->parts[0]->iri);
                self::assertSame($structure['parts'][0]['partNumber'], $resource->parts[0]->partNumber);
                self::assertSame($structure['parts'][0]['description'], $resource->parts[0]->description);
                self::assertSame($structure['parts'][0]['quantity'], (int) $resource->parts[0]->quantity);
                self::assertSame($structure['parts'][0]['unitOfMeasure'], $resource->parts[0]->unitOfMeasure);
            }

            if (count($structure['files'] ?? []) > 0) {
                self::assertSame($structure['files'][0]['@id'], $resource->files[0]->iri);
                self::assertSame($structure['files'][0]['id'], $resource->files[0]->id);
                self::assertSame($structure['files'][0]['filePath'], $resource->files[0]->filePath);
                self::assertSame($structure['files'][0]['createdAt'], $resource->files[0]->createdAt);
                self::assertSame($structure['files'][0]['description'], $resource->files[0]->description);
                self::assertSame($structure['files'][0]['sha'], $resource->files[0]->sha);
                self::assertSame($structure['files'][0]['mimeType'], $resource->files[0]->mimeType);
                self::assertSame($structure['files'][0]['extension'], $resource->files[0]->extension);
                self::assertSame($structure['files'][0]['size'], $resource->files[0]->size);
                self::assertSame($structure['files'][0]['poster']['@id'], $resource->files[0]->poster->iri);
                self::assertSame($structure['files'][0]['poster']['username'], $resource->files[0]->poster->username);
                self::assertSame($structure['files'][0]['poster']['email'], $resource->files[0]->poster->email);
                self::assertSame($structure['files'][0]['poster']['firstname'], $resource->files[0]->poster->firstname);
                self::assertSame($structure['files'][0]['poster']['lastname'], $resource->files[0]->poster->lastname);
            }

            if (count($structure['activity'] ?? []) > 0) {
                self::assertSame($structure['activity'][0]['@id'], $resource->activities[0]->iri);
                self::assertSame($structure['activity'][0]['resource'], $resource->activities[0]->resource);
                self::assertSame($structure['activity'][0]['message'], $resource->activities[0]->message);
                self::assertSame($structure['activity'][0]['createdAt'], $resource->activities[0]->createdAt);
                self::assertSame($structure['activity'][0]['updatedAt'], $resource->activities[0]->updatedAt);
                self::assertSame($structure['activity'][0]['public'], $resource->activities[0]->public);
                self::assertSame($structure['activity'][0]['metadata'], $resource->activities[0]->metadata);
                self::assertSame($structure['activity'][0]['files'], $resource->activities[0]->files);

                self::assertSame($structure['activity'][0]['user']['@id'], $resource->activities[0]->user->iri);
                self::assertSame($structure['activity'][0]['user']['username'], $resource->activities[0]->user->username);
                self::assertSame($structure['activity'][0]['user']['email'], $resource->activities[0]->user->email);
                self::assertSame($structure['activity'][0]['user']['firstname'], $resource->activities[0]->user->firstname);
                self::assertSame($structure['activity'][0]['user']['lastname'], $resource->activities[0]->user->lastname);
            }
        }

        self::assertSame($structure['poster']['@id'], $resource->poster->iri);
        self::assertSame($structure['poster']['username'], $resource->poster->username);
        self::assertSame($structure['poster']['email'], $resource->poster->email);
        self::assertSame($structure['poster']['firstname'], $resource->poster->firstname);
        self::assertSame($structure['poster']['lastname'], $resource->poster->lastname);

        self::assertSame($structure['leader']['@id'], $resource->poster->iri);
        self::assertSame($structure['leader']['username'], $resource->poster->username);
        self::assertSame($structure['leader']['email'], $resource->poster->email);
        self::assertSame($structure['leader']['firstname'], $resource->poster->firstname);
        self::assertSame($structure['leader']['lastname'], $resource->poster->lastname);

        self::assertSame($structure['iFactor'], $resource->iFactor);

        self::assertSame($structure['factory']['@id'], $resource->factory->iri);
        self::assertSame($structure['factory']['name'], $resource->factory->name);
        self::assertSame($structure['factory']['erp'], $resource->factory->erp);

        self::assertSame($structure['supplierErp'], $resource->supplierErp);
        self::assertSame($structure['supplierNumber'], $resource->supplierNumber);
        self::assertSame($structure['supplierName'], $resource->supplierName);

        self::assertSame($structure['status'], $resource->status->value);
        self::assertSame($structure['createdAt'], $resource->createdAt);
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
