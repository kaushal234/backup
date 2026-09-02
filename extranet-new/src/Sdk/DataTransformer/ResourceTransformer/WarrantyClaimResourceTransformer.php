<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\WarrantyClaim;
use App\Sdk\Resource\WarrantyClaimPart;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

class WarrantyClaimResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return WarrantyClaim::class === $resource && WarrantyClaim::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): ResourceInterface
    {
        try {
            /** @var array<string, mixed> $structure */
            $structure = WarrantyClaim::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a Warranty Claim structure.', previous: $e);
        }

        $parts = Vec\map(
            $structure['parts'] ?? [],
            static function (array $part): WarrantyClaimPart {
                $spr = $part['spr'] ?? null;

                return new WarrantyClaimPart(
                    partNumber: $part['partNumber'],
                    partDescription: $part['partDescription'],
                    quantity: $part['quantity'],
                    unitOfMeasure: $part['unitOfMeasure'],
                    sprNumber: $spr['id'] ?? null,
                    sprStatus: $spr['status'] ?? null,
                    sprCreatedAt: $spr['createdAt'] ?? null,
                );
            }
        );

        return new WarrantyClaim(
            iri: $structure['@id'],
            id: (int) basename($structure['@id']),
            status: $structure['status'],
            claimDate: $structure['claimDate'],
            type: $structure['type'] ?? null,
            equipmentModel: $structure['equipmentModel'] ?? null,
            serialNumber: $structure['serialNumber'],
            customerName: $structure['customerName'] ?? null,
            equipmentLocation: $structure['equipmentLocation'] ?? null,
            equipmentHours: $structure['equipmentHours'] ?? null,
            claimantDetails: $structure['claimantDetails'] ?? null,
            details: $structure['details'] ?? null,
            enteredByUsername: $structure['enteredBy']['username'] ?? null,
            description: $structure['description'] ?? null,
            parts: $parts,
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        throw new FailedTransformationException('Failed to transform the given data into a list of Warranty Claims structure.');
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return WarrantyClaim::class === $resource && WarrantyClaim::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = WarrantyClaim::getPageTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Warranty Claims structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        $items = Vector::fromArray($members)->map(static function (array $structure) {
            return new WarrantyClaim(
                iri: $structure['@id'],
                id: (int) basename($structure['@id']),
                status: $structure['status'],
                claimDate: $structure['claimDate'],
                type: $structure['type'] ?? null,
                equipmentModel: $structure['equipmentModel'] ?? null,
                serialNumber: $structure['serialNumber'],
                customerName: $structure['customerName'] ?? null,
                equipmentLocation: $structure['equipmentLocation'] ?? null,
                description: $structure['description'] ?? null,
            );
        });

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }
}
