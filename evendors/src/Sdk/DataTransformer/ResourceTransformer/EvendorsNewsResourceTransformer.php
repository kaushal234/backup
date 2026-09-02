<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\EvendorsNews;
use App\Sdk\Resource\EvendorsNewsFile;
use DateTime;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type\Exception\AssertException;
use Psl\Vec;

/**
 * @implements ResourceTransformerInterface<EvendorsNews>
 */
class EvendorsNewsResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (EvendorsNews::class !== $resource) {
            return false;
        }

        return EvendorsNews::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): EvendorsNews
    {
        try {
            $structure = EvendorsNews::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a representative structure.', previous: $e);
        }

        return new EvendorsNews(
            iri: $structure['@id'],
            id: $structure['id'],
            content: $structure['content'],
            publishedAt: (new DateTime($structure['publishedAt']))->format('l jS \o\f F Y h:i:s A'),
            files: Vec\map($structure['files'] ?? [], static fn ($file) => new EvendorsNewsFile(
                iri: $file['@id'],
                resource: $file['@type'],
                id: $file['id'],
                filePath: $file['filePath'],
                extension: $file['extension'],
            ))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (EvendorsNews::class !== $resource) {
            return false;
        }

        return EvendorsNews::getCollectionTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = EvendorsNews::getCollectionTypeStructure()->assert($data);
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
        throw new FailedTransformationException('Pagination is not supported for RFQ resource.');
    }
}
