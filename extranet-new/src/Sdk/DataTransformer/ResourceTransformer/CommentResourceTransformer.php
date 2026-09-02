<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Comment;
use App\Sdk\Resource\File;
use App\Sdk\Resource\People;
use App\Sdk\Utils\IriToId;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type\Exception\AssertException;

class CommentResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        return Comment::class === $resource && Comment::getTypeStructure()->matches($data);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(mixed $data): Comment
    {
        try {
            $structure = Comment::getTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a Comment structure.', previous: $e);
        }

        return self::buildObject($structure);
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return Comment::class === $resource && Comment::getCollectionTypeStructure()->matches($data);
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = Comment::getCollectionTypeStructure()->assert($data);
        } catch (AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of Comment structure.', previous: $e);
        }

        /** @var array<string, mixed> $members */
        $members = $collection['hydra:member'];

        return Vector::fromArray($members)->map($this->transform(...));
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return Comment::class === $resource && Comment::getPageTypeStructure()->matches($data);
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = Comment::getPageTypeStructure()->assert($data);
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
    public static function buildObject(array $structure): Comment
    {
        return new Comment(
            iri: $structure['@id'],
            id: $structure['id'],
            message: $structure['message'],
            createdAt: new \DateTime($structure['createdAt']),
            user: null !== $structure['user'] ? new People(
                iri: $structure['user']['@id'],
                id: IriToId::iriToId($structure['user']['@id']),
                lastname: $structure['user']['lastname'],
                firstname: $structure['user']['firstname'],
                email: $structure['user']['email'],
            ) : null,
            file: isset($structure['files'][0]) ? new File(
                iri: $structure['files'][0]['@id'],
                id: IriToId::iriToId($structure['files'][0]['@id']),
                filePath: $structure['files'][0]['filePath'],
            ) : null,
        );
    }
}
