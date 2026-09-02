<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Category;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Resource\File;
use App\Sdk\Resource\Manual;
use App\Sdk\Resource\ManualDocument;
use App\Sdk\Resource\Part;
use App\Sdk\Resource\People;
use App\Sdk\Utils\IriToId;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

class ManualDocumentResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return ManualDocument::class === $resource && ManualDocument::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): ManualDocument
    {
        try {
            $structure = ManualDocument::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an Manual Document structure.', previous: $e);
        }

        return new ManualDocument(
            iri: $structure['@id'],
            id: IriToId::iriToId($structure['@id']),
            position: $structure['position'],
            description: $structure['description'],
            type: $structure['type'],
            factoryNumber: $structure['factoryNumber'],
            revision: $structure['revision'] ?? null,
            category: null !== $structure['category'] ? new Category(
                iri: $structure['category']['@id'],
                name: $structure['category']['name'],
            ) : null,
            otherDescription: $structure['otherDescription'],
            manual: new Manual(
                iri: $structure['manual']['@id'],
                id: IriToId::iriToId($structure['manual']['@id']),
                equipment: new EquipmentRecord(
                    iri: $structure['manual']['equipmentRecord']['@id'],
                    id: $structure['manual']['equipmentRecord']['id'],
                    serialNumber: $structure['manual']['equipmentRecord']['serialNumber'],
                    product: $structure['manual']['equipmentRecord']['model'],
                    legacyId: $structure['manual']['equipmentRecord']['legacyId'],
                )
            ),
            document: null !== $structure['document'] ? new File(
                iri: $structure['document']['@id'],
                id: IriToId::iriToId($structure['document']['@id']),
                filePath: $structure['document']['filePath'],
                description: $structure['document']['description'],
                poster: null !== ($structure['document']['poster'] ?? null) ? new People(
                    iri: $structure['document']['poster']['@id'],
                    id: IriToId::iriToId($structure['document']['poster']['@id']),
                    lastname: $structure['document']['poster']['lastname'],
                    firstname: $structure['document']['poster']['firstname'],
                    email: $structure['document']['poster']['email'],
                ) : null,
                createdAt: new \DateTime($structure['document']['createdAt']),
                extension: $structure['document']['extension'],
                size: $structure['document']['size'] ?? null,
            ) : null,
            parts: Vec\map($structure['parts'] ?? [], static function ($part) {
                return new Part(
                    iri: $part['@id'],
                    id: IriToId::iriToId($part['@id']),
                    partNumber: $part['partNumber'],
                    quantity: $part['quantity'],
                    position: $part['position'],
                    unitOfMeasure: $part['unitOfMeasure'],
                    description: $part['description'],
                    preventive: $part['preventive'],
                    maintenance: $part['maintenance'],
                    overhaul: $part['overhaul'],
                    critical: $part['critical'],
                    otherDescription: $part['otherDescription'],
                );
            }),
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        throw new FailedTransformationException('Collection is not supported for Manual Document resource.');
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Page is not supported for Manual Document resource.');
    }
}
