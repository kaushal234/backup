<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Airport;
use App\Sdk\Resource\Country;
use App\Sdk\Resource\Customer;
use App\Sdk\Resource\EmissionRating;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Resource\Location;
use App\Sdk\Resource\Manual;
use App\Sdk\Utils\IriToId;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

class EquipmentRecordResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return EquipmentRecord::class === $resource && EquipmentRecord::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): EquipmentRecord
    {
        try {
            $structure = EquipmentRecord::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an Equipment Record structure.', previous: $e);
        }

        $customer = null !== $structure['endUser'] ? new Customer(
            iri: $structure['endUser']['@id'],
            name: $structure['endUser']['name'],
        ) : null;

        $airport = !empty($structure['airport']) ? new Airport(
            iri: $structure['airport']['@id'],
            id: IriToId::iriToId($structure['airport']['@id']),
            code: $structure['airport']['code'],
            city: $structure['airport']['cityName'] ?? null,
            name: $structure['airport']['name'] ?? null,
            country: null !== $structure['airport']['country'] ? new Country(
                iri: $structure['airport']['country']['@id'],
                id: IriToId::iriToId($structure['airport']['@id']),
                name: $structure['airport']['country']['name'],
            ) : null,
        ) : null;

        $salesOrganisationService = null !== $structure['salesOrganisationService'] ? new Location(
            iri: $structure['salesOrganisationService']['@id'],
            name: $structure['salesOrganisationService']['name'],
        ) : null;

        return new EquipmentRecord(
            iri: $structure['@id'],
            id: $structure['id'],
            serialNumber: $structure['serialNumber'],
            product: $structure['model'],
            legacyId: $structure['legacyId'],
            optionsDescription: $structure['optionsDescription'] ?? null,
            customer: $customer,
            productType: $structure['type'],
            airport: $airport,
            customerSerialNumber: $structure['customerSerialNumber'],
            location: $structure['location'],
            manufacturerLocationErp: $structure['manufacturerLocation']['erp'] ?? null,
            projectNumber: $structure['projectNumber'] ?? null,
            salesOrganisationService: $salesOrganisationService,
            manuals: Vec\map(
                $structure['manuals'] ?? [],
                static function (array $manual): Manual {
                    return new Manual(
                        iri: $manual['@id'],
                        id: IriToId::iriToId($manual['@id']),
                        createdAt: $manual['createdAt'] ? new \DateTime($manual['createdAt']) : null,
                        features: $manual['features'],
                        description: $manual['description'],
                        language: ($manual['language'] ?? null) ?: null,
                    );
                }
            ),
            dateShipped: null !== $structure['dateShipped'] ? new \DateTime($structure['dateShipped']) : null,
            dateCommissioned: null !== $structure['dateCommissioned'] ? new \DateTime($structure['dateCommissioned']) : null,
            emissionRating: !empty($structure['emissionRating']) ?
                new EmissionRating(
                    iri: $structure['emissionRating']['@id'],
                    id: $structure['emissionRating']['id'],
                    name: $structure['emissionRating']['name'],
                    obsolete: $structure['emissionRating']['obsolete'],
                    legacyId: $structure['emissionRating']['legacyId'],
                )
                : null,
            hourMeter: $structure['hourMeter'] ?? null,
            warrantyEndDate: null !== ($structure['warrantyEndDate'] ?? null) ? new \DateTime($structure['warrantyEndDate']) : null,
            warrantyConditions: $structure['warrantyConditions'] ?? null,
            warrantyStatus: $structure['warrantyStatus'] ?? null,
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return EquipmentRecord::class === $resource && EquipmentRecord::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = EquipmentRecord::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Equipment Record structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return EquipmentRecord::class === $resource && EquipmentRecord::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = EquipmentRecord::getPageTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Equipment Record structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        $items = Vector::fromArray($members)->map(static function (array $structure) {
            $customer = null !== $structure['endUser'] ? new Customer(
                iri: $structure['endUser']['@id'],
                name: $structure['endUser']['name'],
            ) : null;

            $airport = null !== $structure['airport'] ? new Airport(
                iri: $structure['airport']['@id'],
                id: IriToId::iriToId($structure['airport']['@id']),
                code: $structure['airport']['code'],
                city: $structure['airport']['cityName'] ?? null,
                name: $structure['airport']['name'],
                country: null !== $structure['airport']['country'] ? new Country(
                    iri: $structure['airport']['country']['@id'],
                    id: $structure['airport']['country']['id'],
                    name: $structure['airport']['country']['name'],
                ) : null
            ) : null;

            $salesOrganisationService = null !== $structure['salesOrganisationService'] ? new Location(
                iri: $structure['salesOrganisationService']['@id'],
                name: $structure['salesOrganisationService']['name'],
            ) : null;

            return new EquipmentRecord(
                iri: $structure['@id'],
                id: $structure['id'],
                serialNumber: $structure['serialNumber'],
                product: $structure['model'],
                legacyId: $structure['legacyId'],
                optionsDescription: $structure['optionsDescription'] ?? null,
                customer: $customer,
                productType: $structure['type'],
                airport: $airport,
                customerSerialNumber: $structure['customerSerialNumber'],
                location: $structure['location'],
                manufacturerLocationErp: $structure['manufacturerLocation']['erp'] ?? null,
                projectNumber: $structure['projectNumber'] ?? null,
                salesOrganisationService: $salesOrganisationService,
            );
        });

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }
}
