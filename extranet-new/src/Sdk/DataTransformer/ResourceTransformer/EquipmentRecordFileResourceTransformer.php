<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\EquipmentRecordFile;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type\Exception\AssertException;

class EquipmentRecordFileResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return EquipmentRecordFile::class === $resource && EquipmentRecordFile::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): EquipmentRecordFile
    {
        try {
            $structure = EquipmentRecordFile::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an Equipment Record File structure.', previous: $e);
        }

        return new EquipmentRecordFile(
            iri: $structure['@id'],
            id: $structure['id'],
            displayFilename: $structure['displayFilename'],
            description: $structure['description'],
            date: $structure['date'] ? new \DateTime($structure['date']) : null,
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return EquipmentRecordFile::class === $resource && EquipmentRecordFile::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = EquipmentRecordFile::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Equipment Record File structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Pagination is not supported for Equipment Record File.');
    }
}
