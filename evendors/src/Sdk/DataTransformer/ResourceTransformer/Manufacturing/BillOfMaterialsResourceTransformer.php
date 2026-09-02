<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer\Manufacturing;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Manufacturing\BillOfMaterials;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Type;
use Psl\Vec;

/**
 * @phpstan-import-type BillOfMaterialsStructure from BillOfMaterials
 *
 * @psalm-import-type BillOfMaterialsStructure from BillOfMaterials
 *
 * @phpstan-import-type BillOfMaterialsItemStructure from BillOfMaterials
 *
 * @psalm-import-type BillOfMaterialsItemStructure from BillOfMaterials
 */
final class BillOfMaterialsResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return BillOfMaterials::class === $resource && BillOfMaterials::getTypeStructure()->matches($data);
    }

    /**
     * @param BillOfMaterialsStructure $data
     */
    public function transform(mixed $data): BillOfMaterials
    {
        try {
            $structure = BillOfMaterials::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a Bill of materials structure.', previous: $e);
        }

        $bom = new BillOfMaterials(
            iri: $structure['@id'],
            site: $structure['site'],
            partNumber: $structure['product'],
            description: $structure['itemDescription'],
            quantity: $structure['quantity'] ?? 1,
            unitOfMeasure: $structure['unitOfMeasure'],
            revision: $structure['engineeringRevision'],
            effectiveDate: $structure['engineeringRevisionEffectiveDate'],
            expiryDate: $structure['engineeringRevisionExpiryDate'],
            expired: $structure['expired'],
            level: 0,
            children: $this->recursiveChildren($structure['items'], $structure['site']),
        );

        return $bom;
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        throw new FailedTransformationException('Collection is not supported for Bill of materials resource.');
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Pagination is not supported for Bill of materials resource.');
    }

    /**
     * @param array<BillOfMaterialsItemStructure> $structure
     *
     * @return array<BillOfMaterials>
     */
    private function recursiveChildren(array $structure, int $site): array
    {
        $billOfMaterialTransformer = $this;

        return Vec\map($structure, static function ($billOfMaterials) use ($billOfMaterialTransformer, $site) {
            return new BillOfMaterials(
                iri: $billOfMaterials['@id'],
                site: $site,
                partNumber: $billOfMaterials['partNumber'],
                description: $billOfMaterials['itemDescription'],
                quantity: $billOfMaterials['quantity'],
                unitOfMeasure: $billOfMaterials['unitOfMeasure'],
                revision: $billOfMaterials['engineeringRevision'],
                effectiveDate: $billOfMaterials['engineeringRevisionEffectiveDate'],
                expiryDate: $billOfMaterials['engineeringRevisionExpiryDate'],
                expired: $billOfMaterials['expired'],
                level: $billOfMaterials['level'],
                children: $billOfMaterialTransformer->recursiveChildren($billOfMaterials['children'], $site),
            );
        });
    }
}
