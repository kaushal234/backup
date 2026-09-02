<?php

declare(strict_types=1);

namespace Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\DataTransformer\ResourceTransformer\TechnicianOnCallResourceTransformer;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\TechnicianOnCall;
use App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer\AbstractResourceTransformerTest;

/**
 * @group unit
 */
final class TechnicianOnCallResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield [[
            '@id' => '/technician_on_calls/42',
            '@type' => 'TechnicianOnCall',
            'id' => 42,
            'confidential' => false,
            'title' => 'toc title en',
            'originalTitle' => 'toc title',
            'description' => 'toc desc en',
            'originalDescription' => 'toc desc',
            'status' => 'status',
            'createdAt' => '2025-06-01',
            'updatedAt' => null,
            'createdBy' => null,
            'equipmentRecord' => null,
            'assignee' => null,
            'unitOperationalStatus' => null,
            'technicianOnCallType' => [
                '@id' => '/technician_on_call_types/42',
                'id' => 10,
                'name' => 'type name',
                'description' => 'type desc',
            ],
            'serviceActivity' => [
                '@id' => '/service_activity/42',
                'id' => 10,
                'name' => 'type name',
                'description' => 'type desc',
            ],
            'indiceFactor' => 'IF 10',
            'airport' => [
                '@id' => '/airports/42',
                'id' => 10,
                'code' => 'airport code',
            ],
            'salesOrganisationService' => [
                '@id' => '/sales_organisation_services/42',
                'name' => 'BU name',
            ],
            'factoryFlag' => true,
            'customer' => [
                '@id' => '/customers/42',
                'name' => 'customer name',
            ],
            'tags' => [],
            'survey' => null,
            'token' => null,
            'mainContact' => null,
            'mainFile' => null,
            'openDays' => 42,
            'daysWithoutActivity' => 10,
            'daysWithoutActivityStatus' => 'days status',
            'files' => [],
        ]];

        yield [[
            '@id' => '/technician_on_calls/42',
            '@type' => 'TechnicianOnCall',
            'id' => 42,
            'confidential' => false,
            'title' => 'toc title en',
            'originalTitle' => 'toc title',
            'description' => 'toc desc en',
            'originalDescription' => 'toc desc',
            'status' => 'status',
            'createdAt' => '2025-06-01',
            'updatedAt' => '2025-06-01',
            'createdBy' => [
                '@id' => '/people/42',
                'id' => 42,
                'lastname' => 'Doe',
                'firstname' => 'John',
                'email' => 'john.doe@exemple.com',
            ],
            'equipmentRecord' => [
                '@id' => '/equipment_records/42',
                'id' => 42,
                'serialNumber' => 'serial number',
                'model' => 'product name',
                'legacyId' => 4242,
                'type' => 'product type',
                'endUser' => null,
                'airport' => null,
                'customerSerialNumber' => null,
                'location' => null,
                'optionsDescription' => null,
                'manuals' => [],
                'salesOrganisationService' => [
                    '@id' => '/locations/1',
                    'name' => 'test location',
                ],
            ],
            'assignee' => [
                '@id' => '/people/42',
                'id' => 42,
                'lastname' => 'Doe',
                'firstname' => 'John',
                'email' => 'john.doe@exemple.com',
            ],
            'unitOperationalStatus' => [
                '@id' => '/unit_operational_status/42',
                'id' => 10,
                'name' => 'unitOperationalStatus name',
                'description' => 'unitOperationalStatus desc',
            ],
            'technicianOnCallType' => [
                '@id' => '/technician_on_call_types/42',
                'id' => 10,
                'name' => 'type name',
                'description' => 'type desc',
            ],
            'serviceActivity' => [
                '@id' => '/service_activity/42',
                'id' => 10,
                'name' => 'type name',
                'description' => 'type desc',
            ],
            'indiceFactor' => 'IF 10',
            'airport' => [
                '@id' => '/airports/42',
                'id' => 10,
                'code' => 'airport code',
            ],
            'salesOrganisationService' => [
                '@id' => '/sales_organisation_services/42',
                'name' => 'BU name',
            ],
            'factoryFlag' => true,
            'customer' => [
                '@id' => '/customers/42',
                'name' => 'customer name',
            ],
            'tags' => [],
            'survey' => [
                '@id' => '/service/technician_on_call_surveys/1',
                'id' => 1,
                'execution' => 2,
                'responsiveness' => 2,
                'communication' => 2,
                'attitude' => 2,
                'comment' => 'My comment',
            ],
            'token' => 'my_token',
            'mainContact' => [
                '@id' => '/users/42',
                'id' => 42,
                'lastname' => 'Doe',
                'firstname' => 'John',
                'email' => 'john.doe@exemple.com',
                'extranetUserProfile' => [
                    '@id' => '/profile/42',
                    'jobTitle' => 'jobTitle',
                    'division' => 'division',
                    'department' => 'department',
                    'language' => 'language',
                    'country' => null,
                ],
                'address' => [
                    'street1' => 'street1',
                    'street2' => 'street2',
                    'city' => 'city',
                    'state' => 'state',
                    'postalCode' => 'postalCode',
                ],
            ],
            'mainFile' => [
                '@id' => '/users/42',
                'id' => 42,
                'filePath' => 'filePath',
                'description' => null,
                'poster' => null,
                'createdAt' => '2025-06-01',
                'extension' => 'jpg',
            ],
            'openDays' => 42,
            'daysWithoutActivity' => 10,
            'daysWithoutActivityStatus' => 'days status',
            'files' => [],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield [[
            '@type' => 'TechnicianOnCall',
            'id' => 13,
        ]];

        yield [[
            '@type' => 'TechnicianOnCall',
            '@id' => 13,
            'title' => 'Kinder',
        ]];

        yield [[]];

        yield [[
            'title' => 'some title',
        ]];
    }

    protected function getResourceClass(): string
    {
        return TechnicianOnCall::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new TechnicianOnCallResourceTransformer();
    }

    /**
     * @param array<string, mixed> $structure
     */
    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(TechnicianOnCall::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['id'], $resource->id);
        self::assertSame($structure['title'], $resource->title);
        self::assertSame($structure['originalTitle'], $resource->originalTitle);
        self::assertSame($structure['description'], $resource->description);
        self::assertSame($structure['originalDescription'], $resource->originalDescription);
        self::assertSame($structure['status'], $resource->status);
        self::assertSame($structure['createdAt'], $resource->createdAt->format('Y-m-d'));
        self::assertSame($structure['updatedAt'], $resource->updatedAt?->format('Y-m-d'));

        self::assertSame($structure['createdBy']['@id'] ?? null, $resource->createdBy?->iri);
        self::assertSame($structure['createdBy']['id'] ?? null, $resource->createdBy?->id);
        self::assertSame($structure['createdBy']['lastname'] ?? null, $resource->createdBy?->lastname);
        self::assertSame($structure['createdBy']['firstname'] ?? null, $resource->createdBy?->firstname);
        self::assertSame($structure['createdBy']['email'] ?? null, $resource->createdBy?->email);

        self::assertSame($structure['equipmentRecord']['legacyId'] ?? null, $resource->equipmentRecord?->legacyId);
        self::assertSame($structure['equipmentRecord']['serialNumber'] ?? null, $resource->equipmentRecord?->serialNumber);
        self::assertSame($structure['equipmentRecord']['model'] ?? null, $resource->equipmentRecord?->product);
        self::assertSame($structure['equipmentRecord']['type'] ?? null, $resource->equipmentRecord?->productType);
        self::assertSame($structure['equipmentRecord']['airport'] ?? null, $resource->equipmentRecord?->airport);
        self::assertSame($structure['equipmentRecord']['customerSerialNumber'] ?? null, $resource->equipmentRecord?->customerSerialNumber);
        self::assertSame($structure['equipmentRecord']['location'] ?? null, $resource->equipmentRecord?->location);
        self::assertSame($structure['equipmentRecord']['optionsDescription'] ?? null, $resource->equipmentRecord?->optionsDescription);
        self::assertSame($structure['equipmentRecord']['manuals'] ?? null, $resource->equipmentRecord?->manuals);

        self::assertSame($structure['assignee']['@id'] ?? null, $resource->assignee?->iri);
        self::assertSame($structure['assignee']['id'] ?? null, $resource->assignee?->id);
        self::assertSame($structure['assignee']['lastname'] ?? null, $resource->assignee?->lastname);
        self::assertSame($structure['assignee']['firstname'] ?? null, $resource->assignee?->firstname);
        self::assertSame($structure['assignee']['email'] ?? null, $resource->assignee?->email);

        self::assertSame($structure['unitOperationalStatus']['name'] ?? null, $resource->unitOperationalStatus?->name);
        self::assertSame($structure['unitOperationalStatus']['description'] ?? null, $resource->unitOperationalStatus?->description);

        self::assertSame($structure['technicianOnCallType']['name'], $resource->technicianOnCallType->name);
        self::assertSame($structure['technicianOnCallType']['description'], $resource->technicianOnCallType->description);

        self::assertSame($structure['serviceActivity']['name'], $resource->serviceActivity->name);
        self::assertSame($structure['serviceActivity']['description'], $resource->serviceActivity->description);

        self::assertSame($structure['indiceFactor'], $resource->indiceFactor);

        self::assertSame($structure['airport']['code'], $resource->airport->code);

        self::assertSame($structure['salesOrganisationService']['name'], $resource->salesOrganisationService->name);

        self::assertSame($structure['factoryFlag'], $resource->factoryFlag);
        self::assertSame($structure['openDays'], $resource->openDays);
        self::assertSame($structure['daysWithoutActivity'], $resource->daysWithoutActivity);
        self::assertSame($structure['daysWithoutActivityStatus'], $resource->daysWithoutActivityStatus);

        self::assertSame($structure['customer']['name'], $resource->customer->name);

        if (!$fromCollectionOrPage) {
            self::assertSame($structure['survey']['execution'] ?? null, $resource->survey?->execution);
            self::assertSame($structure['survey']['responsiveness'] ?? null, $resource->survey?->responsiveness);
            self::assertSame($structure['survey']['communication'] ?? null, $resource->survey?->communication);
            self::assertSame($structure['survey']['attitude'] ?? null, $resource->survey?->attitude);
            self::assertSame($structure['survey']['comment'] ?? null, $resource->survey?->comment);

            self::assertSame($structure['token'] ?? null, $resource->token);
        }

        self::assertSame($structure['mainContact']['lastname'] ?? null, $resource->mainContact?->lastname);
        self::assertSame($structure['mainContact']['firstname'] ?? null, $resource->mainContact?->firstname);
        self::assertSame($structure['mainContact']['email'] ?? null, $resource->mainContact?->email);
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
