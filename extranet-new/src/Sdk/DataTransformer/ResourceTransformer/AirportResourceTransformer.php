<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Airport;
use App\Sdk\Utils\IriToId;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;

class AirportResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return Airport::class === $resource && Airport::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): Airport
    {
        try {
            $structure = Airport::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into an airport structure.', previous: $e);
        }

        return self::buildObject($structure);
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return Airport::class === $resource && Airport::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = Airport::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Airport structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return Airport::class === $resource && Airport::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = Airport::getPageTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Comment structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        $items = Vector::fromArray($members)->map(static function (array $structure) {
            return self::buildObject($structure);
        });

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }

    /**
     * @param array<string, mixed> $structure
     */
    public static function buildObject(array $structure): Airport
    {
        return new Airport(
            iri: $structure['@id'],
            id: IriToId::iriToId($structure['@id']),
            code: $structure['code'],
            city: $structure['cityName'] ?? null,
            name: $structure['name'] ?? null,
        );
    }
}
