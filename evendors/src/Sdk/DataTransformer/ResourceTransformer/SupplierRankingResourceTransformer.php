<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\Supplier;
use App\Sdk\Resource\SupplierRanking\Criteria;
use App\Sdk\Resource\SupplierRanking\Notation;
use App\Sdk\Resource\SupplierRanking\SupplierRanking;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

/**
 * @implements ResourceTransformerInterface<SupplierRanking>
 */
class SupplierRankingResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (SupplierRanking::class !== $resource) {
            return false;
        }

        return SupplierRanking::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): SupplierRanking
    {
        try {
            $structure = SupplierRanking::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a representative structure.', previous: $e);
        }

        $notations = [];
        if (Iter\contains_key($structure, 'notations')) {
            $notations = Vec\map(
                $structure['notations'],
                static function ($data): Notation {
                    $data = Notation::getTypeStructure()->assert($data);

                    return new Notation(
                        iri: $data['@id'],
                        criteria: new Criteria($data['criteria']['@id'], $data['criteria']['id'], $data['criteria']['name'], $data['criteria']['public']),
                        notation: $data['notation'],
                    );
                }
            );
        }

        return new SupplierRanking(
            iri: $structure['@id'],
            id: $structure['id'],
            supplier: new Supplier(
                iri: $structure['supplier']['@id'],
                name: $structure['supplier']['name'],
                code: $structure['supplier']['code']
            ),
            notations: $notations,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (SupplierRanking::class !== $resource) {
            return false;
        }

        return SupplierRanking::getCollectionTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = SupplierRanking::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list comment structure.', previous: $e);
        }

        return Vector::fromArray($collection['hydra:member'])->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): never
    {
        throw new FailedTransformationException('Pagination is not supported for Supplier Ranking resource.');
    }
}
