<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Airport;
use App\Sdk\Resource\Customer;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Resource\File;
use App\Sdk\Resource\Location;
use App\Sdk\Resource\People;
use App\Sdk\Resource\ServiceActivity;
use App\Sdk\Resource\TechnicianOnCall;
use App\Sdk\Resource\TechnicianOnCallSurvey;
use App\Sdk\Resource\TechnicianOnCallType;
use App\Sdk\Resource\UnitOperationalStatus;
use App\Sdk\Resource\User;
use App\Sdk\Utils\IriToId;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

class TechnicianOnCallResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return TechnicianOnCall::class === $resource && TechnicianOnCall::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): TechnicianOnCall
    {
        try {
            $structure = TechnicianOnCall::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a technician on call structure.', previous: $e);
        }

        return new TechnicianOnCall(
            iri: $structure['@id'],
            id: $structure['id'],
            confidential: $structure['confidential'],
            title: $structure['title'],
            description: $structure['description'],
            status: $structure['status'],
            createdAt: new \DateTime($structure['createdAt']),
            technicianOnCallType: new TechnicianOnCallType(
                iri: $structure['technicianOnCallType']['@id'],
                id: $structure['technicianOnCallType']['id'],
                name: $structure['technicianOnCallType']['name'],
                description: $structure['technicianOnCallType']['description'],
            ),
            serviceActivity: new ServiceActivity(
                iri: $structure['serviceActivity']['@id'],
                id: $structure['serviceActivity']['id'],
                name: $structure['serviceActivity']['name'],
                description: $structure['serviceActivity']['description'],
            ),
            indiceFactor: $structure['indiceFactor'],
            airport: new Airport(
                iri: $structure['airport']['@id'],
                id: IriToId::iriToId($structure['airport']['@id']),
                code: $structure['airport']['code'],
                city: $structure['airport']['cityName'] ?? null,
                name: $structure['airport']['name'] ?? null,
            ),
            salesOrganisationService: new Location(
                iri: $structure['salesOrganisationService']['@id'],
                name: $structure['salesOrganisationService']['name'],
            ),
            factoryFlag: $structure['factoryFlag'],
            customer: new Customer(
                iri: $structure['customer']['@id'],
                name: $structure['customer']['name'],
            ),
            openDays: $structure['openDays'],
            daysWithoutActivity: $structure['daysWithoutActivity'],
            daysWithoutActivityStatus: $structure['daysWithoutActivityStatus'],
            originalTitle: $structure['originalTitle'],
            originalDescription: $structure['originalDescription'],
            updatedAt: null !== $structure['updatedAt'] ? new \DateTime($structure['updatedAt']) : null,
            createdBy: null !== $structure['createdBy'] ? new People(
                iri: $structure['createdBy']['@id'],
                id: IriToId::iriToId($structure['createdBy']['@id']),
                lastname: $structure['createdBy']['lastname'],
                firstname: $structure['createdBy']['firstname'],
                email: $structure['createdBy']['email'],
            ) : null,
            equipmentRecord: null !== $structure['equipmentRecord'] ? new EquipmentRecord(
                iri: $structure['equipmentRecord']['@id'],
                id: $structure['equipmentRecord']['id'],
                serialNumber: $structure['equipmentRecord']['serialNumber'],
                product: $structure['equipmentRecord']['model'],
                legacyId: $structure['equipmentRecord']['legacyId'],
                productType: $structure['equipmentRecord']['type'],
            ) : null,
            assignee: null !== $structure['assignee'] ? new People(
                iri: $structure['assignee']['@id'],
                id: IriToId::iriToId($structure['assignee']['@id']),
                lastname: $structure['assignee']['lastname'],
                firstname: $structure['assignee']['firstname'],
                email: $structure['assignee']['email'],
            ) : null,
            unitOperationalStatus: null !== $structure['unitOperationalStatus'] ? new UnitOperationalStatus(
                iri: $structure['unitOperationalStatus']['@id'],
                id: $structure['unitOperationalStatus']['id'],
                name: $structure['unitOperationalStatus']['name'],
                description: $structure['unitOperationalStatus']['description'],
            ) : null,
            mainContact: null !== $structure['mainContact'] ? new User(
                iri: $structure['mainContact']['@id'],
                id: $structure['mainContact']['id'],
                profileIri: $structure['mainContact']['extranetUserProfile']['@id'],
                lastname: $structure['mainContact']['lastname'],
                firstname: $structure['mainContact']['firstname'],
                email: $structure['mainContact']['email'],
            ) : null,
            survey: null !== $structure['survey'] ? new TechnicianOnCallSurvey(
                iri: $structure['survey']['@id'],
                id: $structure['survey']['id'],
                execution: $structure['survey']['execution'],
                responsiveness: $structure['survey']['responsiveness'],
                communication: $structure['survey']['communication'],
                attitude: $structure['survey']['attitude'],
                comment: $structure['survey']['comment'],
            ) : null,
            token: $structure['token'],
            mainFile: null,
            files: Vec\map(
                $structure['files'] ?? [],
                static function (array $file): File {
                    return new File(
                        iri: $file['@id'],
                        id: IriToId::iriToId($file['@id']),
                        filePath: $file['filePath'],
                        description: $file['description'],
                        poster: null !== $file['poster'] ? new People(
                            iri: $file['poster']['@id'],
                            id: IriToId::iriToId($file['poster']['@id']),
                            lastname: $file['poster']['lastname'],
                            firstname: $file['poster']['firstname'],
                            email: $file['poster']['email'],
                        ) : null,
                        createdAt: null !== $file['createdAt'] ? new \DateTime($file['createdAt']) : null,
                    );
                }
            ),
            tags: [],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return TechnicianOnCall::class === $resource && TechnicianOnCall::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = TechnicianOnCall::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of technician on call structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return TechnicianOnCall::class === $resource && TechnicianOnCall::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = TechnicianOnCall::getPageTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of TechnicianOnCall structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        $items = Vector::fromArray($members)->map(static function (array $structure) {
            return new TechnicianOnCall(
                iri: $structure['@id'],
                id: $structure['id'],
                confidential: $structure['confidential'],
                title: $structure['title'],
                description: $structure['description'],
                status: $structure['status'],
                createdAt: new \DateTime($structure['createdAt']),
                technicianOnCallType: new TechnicianOnCallType(
                    iri: $structure['technicianOnCallType']['@id'],
                    id: $structure['technicianOnCallType']['id'],
                    name: $structure['technicianOnCallType']['name'],
                    description: $structure['technicianOnCallType']['description'],
                ),
                serviceActivity: new ServiceActivity(
                    iri: $structure['serviceActivity']['@id'],
                    id: $structure['serviceActivity']['id'],
                    name: $structure['serviceActivity']['name'],
                    description: $structure['serviceActivity']['description'],
                ),
                indiceFactor: $structure['indiceFactor'],
                airport: new Airport(
                    iri: $structure['airport']['@id'],
                    id: IriToId::iriToId($structure['airport']['@id']),
                    code: $structure['airport']['code'],
                ),
                salesOrganisationService: new Location(
                    iri: $structure['salesOrganisationService']['@id'],
                    name: $structure['salesOrganisationService']['name'],
                ),
                factoryFlag: $structure['factoryFlag'],
                customer: new Customer(
                    iri: $structure['customer']['@id'],
                    name: $structure['customer']['name'],
                ),
                openDays: $structure['openDays'],
                daysWithoutActivity: $structure['daysWithoutActivity'],
                daysWithoutActivityStatus: $structure['daysWithoutActivityStatus'],
                originalTitle: $structure['originalTitle'],
                originalDescription: $structure['originalDescription'],
                updatedAt: null !== $structure['updatedAt'] ? new \DateTime($structure['updatedAt']) : null,
                createdBy: null !== $structure['createdBy'] ? new People(
                    iri: $structure['createdBy']['@id'],
                    id: IriToId::iriToId($structure['createdBy']['@id']),
                    lastname: $structure['createdBy']['lastname'],
                    firstname: $structure['createdBy']['firstname'],
                    email: $structure['createdBy']['email'],
                ) : null,
                equipmentRecord: null !== $structure['equipmentRecord'] ? new EquipmentRecord(
                    iri: $structure['equipmentRecord']['@id'],
                    id: $structure['equipmentRecord']['id'],
                    serialNumber: $structure['equipmentRecord']['serialNumber'],
                    product: $structure['equipmentRecord']['model'],
                    legacyId: $structure['equipmentRecord']['legacyId'],
                    productType: $structure['equipmentRecord']['type'],
                ) : null,
                assignee: null !== $structure['assignee'] ? new People(
                    iri: $structure['assignee']['@id'],
                    id: IriToId::iriToId($structure['assignee']['@id']),
                    lastname: $structure['assignee']['lastname'],
                    firstname: $structure['assignee']['firstname'],
                    email: $structure['assignee']['email'],
                ) : null,
                unitOperationalStatus: null !== $structure['unitOperationalStatus'] ? new UnitOperationalStatus(
                    iri: $structure['unitOperationalStatus']['@id'],
                    id: $structure['unitOperationalStatus']['id'],
                    name: $structure['unitOperationalStatus']['name'],
                    description: $structure['unitOperationalStatus']['description'],
                ) : null,
                mainContact: null !== $structure['mainContact'] ? new User(
                    iri: $structure['mainContact']['@id'],
                    id: $structure['mainContact']['id'],
                    profileIri: $structure['mainContact']['extranetUserProfile']['@id'],
                    lastname: $structure['mainContact']['lastname'],
                    firstname: $structure['mainContact']['firstname'],
                    email: $structure['mainContact']['email'],
                ) : null,
                mainFile: null,
                tags: [],
            );
        });

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }
}
