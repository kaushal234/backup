<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\EquipmentRecordPublic;
use App\Sdk\Resource\ManualDocumentPublic;
use App\Sdk\Resource\ManualPublic;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

final class EquipmentRecordPublicResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return EquipmentRecordPublic::class === $resource
            && EquipmentRecordPublic::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): EquipmentRecordPublic
    {
        try {
            $structure = EquipmentRecordPublic::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an EquipmentRecordPublic structure.', previous: $e);
        }

        return $this->buildResource($structure);
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return EquipmentRecordPublic::class === $resource
            && EquipmentRecordPublic::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = EquipmentRecordPublic::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of EquipmentRecordPublic structure.', previous: $e);
        }

        /** @var list<array<string, mixed>> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->buildResource(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return EquipmentRecordPublic::class === $resource
            && EquipmentRecordPublic::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = EquipmentRecordPublic::getPageTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a paginated list of EquipmentRecordPublic structure.', previous: $e);
        }

        /** @var list<array<string, mixed>> $members */
        $members = $collection['hydra:member'];

        $items = Vector::fromArray($members)->map($this->buildResource(...));

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }

    /**
     * Builds the resource from an already-validated structure. Used both for single items
     * (which carry `lastManual`) and for collection/page members (which do not).
     *
     * @param array<string, mixed> $structure
     */
    private function buildResource(array $structure): EquipmentRecordPublic
    {
        $lastManual = null;
        if (isset($structure['lastManual'])) {
            $manualData = $structure['lastManual'];

            $documents = Vec\map(
                $manualData['documents'] ?? [],
                static fn (array $doc) => new ManualDocumentPublic(
                    iri: \sprintf('%s/%s', '/public/manual_documents', $doc['id']),
                    id: $doc['id'],
                    description: $doc['description'] ?? null,
                    categoryName: $doc['categoryName'] ?? null,
                    fileId: $doc['fileId'] ?? null,
                    extension: $doc['extension'] ?? null,
                )
            );

            $lastManual = new ManualPublic(
                id: $manualData['id'],
                createdAt: $manualData['createdAt'],
                documents: $documents,
            );
        }

        return new EquipmentRecordPublic(
            iri: $structure['@id'],
            id: $structure['id'],
            serialNumber: $structure['serialNumber'],
            product: $structure['model'],
            productType: $structure['type'],
            airportCode: $structure['airportCode'],
            optionsDescription: $structure['optionsDescription'],
            lastManual: $lastManual,
        );
    }
}
