<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Document;
use App\Sdk\Resource\Person;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type;

/**
 * @implements ResourceTransformerInterface<Document>
 */
final class DocumentResourceTransformer implements ResourceTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function supports(string $resource, mixed $data): bool
    {
        if (Document::class !== $resource) {
            return false;
        }

        return Document::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): Document
    {
        try {
            $structure = Document::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a document structure.', previous: $e);
        }

        return new Document(
            $structure['@id'],
            $structure['id'],
            $structure['legacyId'],
            $structure['title'],
            $structure['subject'],
            $structure['description'],
            $structure['type'],
            $structure['language'],
            $structure['portal'],
            new Person(
                $structure['owner']['@id'],
                $structure['owner']['username'],
                $structure['owner']['email'],
                $structure['owner']['firstname'],
                $structure['owner']['lastname'],
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (Document::class !== $resource) {
            return false;
        }

        return Document::getCollectionTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = Document::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list document structure.', previous: $e);
        }

        return Vector::fromArray($collection['hydra:member'])->map($this->transform(...));
    }

    /**
     * {@inheritDoc}
     */
    public function supportsPage(string $resource, mixed $data): bool
    {
        if (Document::class !== $resource) {
            return false;
        }

        return Document::getPageTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = Document::getPageTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list document structure.', previous: $e);
        }

        $items = Vector::fromArray($collection['hydra:member'])->map($this->transform(...));

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }
}
