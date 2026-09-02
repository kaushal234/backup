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
use App\Sdk\Resource\People;
use App\Sdk\Utils\IriToId;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

class ManualResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return Manual::class === $resource && Manual::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): Manual
    {
        try {
            $structure = Manual::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an Manual structure.', previous: $e);
        }

        return new Manual(
            iri: $structure['@id'],
            id: $structure['id'],
            documents: Vec\map(
                $structure['documents'] ?? [],
                static function (array $document): ManualDocument {
                    return new ManualDocument(
                        iri: $document['@id'],
                        id: IriToId::iriToId($document['@id']),
                        position: $document['position'],
                        description: $document['description'],
                        type: $document['type'],
                        factoryNumber: $document['factoryNumber'],
                        revision: $document['revision'] ?? null,
                        category: null !== $document['category'] ? new Category(
                            iri: $document['category']['@id'],
                            name: $document['category']['name']
                        ) : null,
                        otherDescription: $document['otherDescription'],
                        document: null !== $document['document'] ? new File(
                            iri: $document['document']['@id'],
                            id: IriToId::iriToId($document['document']['@id']),
                            filePath: $document['document']['filePath'],
                            description: $document['document']['description'],
                            poster: null !== $document['document']['poster'] ? new People(
                                iri: $document['document']['poster']['@id'],
                                id: IriToId::iriToId($document['document']['poster']['@id']),
                                lastname: $document['document']['poster']['lastname'],
                                firstname: $document['document']['poster']['firstname'],
                                email: $document['document']['poster']['email'],
                            ) : null,
                            createdAt: new \DateTime($document['document']['createdAt']),
                            extension: $document['document']['extension'],
                        ) : null,
                    );
                }
            ),
            createdAt: null !== $structure['createdAt'] ? new \DateTime($structure['createdAt']) : null,
            features: $structure['features'],
            description: $structure['description'],
            language: $structure['language'] ?? null,
            status: $structure['status'],
            equipment: new EquipmentRecord(
                iri: $structure['equipmentRecord']['@id'],
                id: $structure['equipmentRecord']['id'],
                serialNumber: $structure['equipmentRecord']['serialNumber'],
                product: $structure['equipmentRecord']['model'],
                legacyId: $structure['equipmentRecord']['legacyId'],
            )
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        throw new FailedTransformationException('Collection is not supported for Manual resource.');
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Page is not supported for Manual resource.');
    }
}
