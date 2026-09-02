<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\ServiceBulletinFile;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Type\Exception\AssertException;

class ServiceBulletinFileResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return ServiceBulletinFile::class === $resource && ServiceBulletinFile::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): ServiceBulletinFile
    {
        try {
            $structure = ServiceBulletinFile::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a SB File structure.', previous: $e);
        }

        return new ServiceBulletinFile(
            iri: $structure['@id'],
            id: $structure['id'],
            filePath: $structure['filePath'],
            description: $structure['description'],
            createdAt: $structure['createdAt'] ? new \DateTime($structure['createdAt']) : null,
            extension: $structure['extension'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return ServiceBulletinFile::class === $resource && ServiceBulletinFile::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = ServiceBulletinFile::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of SB File structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        throw new FailedTransformationException('Failed to transform the given data into a list of Equipment Records structure.');
    }
}
