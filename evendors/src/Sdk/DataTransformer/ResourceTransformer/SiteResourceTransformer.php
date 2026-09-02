<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Site;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type;

/**
 * @implements ResourceTransformerInterface<Site>
 */
final class SiteResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return Site::class === $resource && Site::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): Site
    {
        try {
            $structure = Site::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a site structure.', previous: $e);
        }

        return new Site(
            iri: $structure['@id'],
            siteID: $structure['siteID'],
            siteDescription: $structure['siteDescription'],
            siteAddressCode: $structure['siteAddressCode'],
            siteAddressName: $structure['siteAddressName'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return Site::class === $resource && Site::getCollectionTypeStructure()->matches($data);
    }

    /**
     * @return Vector<Site>
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $structures = Site::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of site structures.', previous: $e);
        }

        $results = [];
        foreach ($structures['hydra:member'] as $structure) {
            $results[] = $this->transform($structure);
        }

        return new Vector($results);
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return Site::class === $resource && Site::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $structures = Site::getPageTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of site structures.', previous: $e);
        }

        $results = [];
        foreach ($structures['hydra:member'] as $structure) {
            $results[] = $this->transform($structure);
        }

        $totalItems = $structures['hydra:totalItems'];
        $hasNext = Iter\contains_key($structures['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($structures['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, new Vector($results));
    }
}
