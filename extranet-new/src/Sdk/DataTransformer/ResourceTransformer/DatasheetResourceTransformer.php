<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Datasheet;
use App\Sdk\Resource\Document;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type\Exception\AssertException;

class DatasheetResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return Datasheet::class === $resource && Datasheet::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): Datasheet
    {
        try {
            $structure = Datasheet::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a datasheet structure.', previous: $e);
        }

        return new Datasheet(
            iri: $structure['@id'],
            id: $structure['id'],
            document: new Document(
                iri: $structure['dms']['@id'],
                id: $structure['dms']['id'],
                legacyId: $structure['dms']['legacyId'],
                type: $structure['dms']['type'],
                title: $structure['dms']['title'],
                description: $structure['dms']['description'],
                language: $structure['dms']['language'],
                createdAt: (new \DateTime($structure['dms']['createdAt']))->format('Y-m-d')
            ),
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return Datasheet::class === $resource && Datasheet::getCollectionTypeStructure()->matches($data);
    }

    /**
     * @return Vector<Datasheet>
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = Datasheet::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of datasheet structure.', previous: $e);
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
        throw new FailedTransformationException('Pagination is not supported for Datasheet resource.');
    }
}
