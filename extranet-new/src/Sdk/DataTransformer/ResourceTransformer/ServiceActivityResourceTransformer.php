<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\ServiceActivity;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;

class ServiceActivityResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return ServiceActivity::class === $resource && ServiceActivity::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): ServiceActivity
    {
        try {
            $structure = ServiceActivity::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a service activity structure.', previous: $e);
        }

        return new ServiceActivity(
            iri: $structure['@id'],
            id: $structure['id'],
            name: $structure['name'],
            description: $structure['description'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return ServiceActivity::class === $resource && ServiceActivity::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = ServiceActivity::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of service activity structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return ServiceActivity::class === $resource && ServiceActivity::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = ServiceActivity::getPageTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of service activity structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        $items = Vector::fromArray($members)->map(static function (array $structure) {
            return new ServiceActivity(
                iri: $structure['@id'],
                id: $structure['id'],
                name: $structure['name'],
                description: $structure['description'],
            );
        });

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }
}
