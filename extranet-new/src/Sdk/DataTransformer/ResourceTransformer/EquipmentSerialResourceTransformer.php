<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\EquipmentSerial;
use App\Sdk\Resource\EquipmentSerialComponent;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type\Exception\AssertException;

class EquipmentSerialResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return EquipmentSerial::class === $resource && EquipmentSerial::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): EquipmentSerial
    {
        try {
            $structure = EquipmentSerial::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an Equipment Serial structure.', previous: $e);
        }

        $component = null !== $structure['component']
            ? new EquipmentSerialComponent(
                id: $structure['component']['id'],
                name: $structure['component']['name'],
                signalCode: $structure['component']['signalCode'],
            )
            : null;

        return new EquipmentSerial(
            iri: $structure['@id'],
            id: $structure['id'],
            legacyId: $structure['legacyId'],
            component: $component,
            model: $structure['model'],
            serial: $structure['serial'],
            brand: $structure['brand'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return EquipmentSerial::class === $resource && EquipmentSerial::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = EquipmentSerial::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Equipment Serial structure.', previous: $e);
        }

        $result = $this->getCollectionMembers($collection['hydra:member']);

        return Vector::fromArray($result);
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Pagination is not supported for EquipmentSerial resource.');
    }

    /**
     * @param array<int, array<string, mixed>> $members
     *
     * @return array<EquipmentSerial>
     */
    private function getCollectionMembers(array $members): array
    {
        $result = [];

        foreach ($members as $structure) {
            $component = null !== ($structure['component'] ?? null)
                ? new EquipmentSerialComponent(
                    id: $structure['component']['id'],
                    name: $structure['component']['name'],
                    signalCode: $structure['component']['signalCode'],
                )
                : null;

            $result[] = new EquipmentSerial(
                iri: $structure['@id'],
                id: $structure['id'],
                legacyId: $structure['legacyId'],
                component: $component,
                model: $structure['model'] ?? null,
                serial: $structure['serial'] ?? null,
                brand: $structure['brand'] ?? null,
            );
        }

        return $result;
    }
}
