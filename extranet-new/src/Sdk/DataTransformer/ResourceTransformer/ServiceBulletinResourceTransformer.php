<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\ServiceBulletin;
use App\Sdk\Resource\ServiceBulletinEquipment;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

class ServiceBulletinResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return ServiceBulletin::class === $resource && ServiceBulletin::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): ServiceBulletin
    {
        try {
            $structure = ServiceBulletin::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a SB structure.', previous: $e);
        }

        $equipments = Vec\map(
            $structure['lines'] ?? [],
            static function (array $line): ServiceBulletinEquipment {
                $equipment = $line['equipmentRecord'];

                return new ServiceBulletinEquipment(
                    id: $equipment['id'],
                    serialNumber: $equipment['serialNumber'],
                    model: $equipment['model'],
                    type: $equipment['type'],
                    customerName: $equipment['customerName'],
                );
            }
        );

        return new ServiceBulletin(
            iri: $structure['@id'],
            id: $structure['id'],
            createdAt: $structure['createdAt'],
            confidential: $structure['confidential'],
            title: $structure['title'],
            partsNeeded: $structure['partsNeeded'] ?? false,
            description: $structure['description'] ?? null,
            type: $structure['type'] ?? null,
            category: $structure['category'] ?? null,
            status: $structure['status'] ?? null,
            ssdDecidedAt: $structure['ssdDecidedAt'] ?? null,
            equipments: $equipments,
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        throw new FailedTransformationException('Failed to transform the given data into a list of Equipment Records structure.');
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return ServiceBulletin::class === $resource && ServiceBulletin::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = ServiceBulletin::getPageTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of SB structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        $items = Vector::fromArray($members)->map(static function (array $structure) {
            return new ServiceBulletin(
                iri: $structure['@id'],
                id: $structure['id'],
                createdAt: $structure['createdAt'],
                confidential: $structure['confidential'],
                title: $structure['title'],
                partsNeeded: $structure['partsNeeded'] ?? false,
                description: $structure['description'] ?? null,
                type: $structure['type'] ?? null,
                category: $structure['category'] ?? null,
                status: $structure['status'] ?? null,
                ssdDecidedAt: $structure['ssdDecidedAt'] ?? null,
            );
        });

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }
}
