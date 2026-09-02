<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\ResourceInterface;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @template T of ResourceInterface
 */
interface ResourceTransformerInterface
{
    /**
     * Return true if this transformer is capable of converting $data into a $resource instance.
     *
     * @param class-string $resource
     *
     * @psalm-assert-if-true class-string<T> $resource
     */
    public function supports(string $resource, mixed $data): bool;

    /**
     * Transform the given data into a resource instance.
     *
     * @return T
     *
     * @throws FailedTransformationException if failed to transform the given data
     */
    public function transform(mixed $data): ResourceInterface;

    /**
     * Return true if this transformer is capable of converting $data into a collection of $resource instances.
     *
     * @param class-string $resource
     *
     * @psalm-assert-if-true class-string<T> $data
     */
    public function supportsCollection(string $resource, mixed $data): bool;

    /**
     * Transform the given data into a collection of resource instances.
     *
     * @return AccessibleCollectionInterface<T>
     *
     * @throws FailedTransformationException if failed to transform the given data
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface;

    /**
     * Return true if this transformer is capable of converting $data into a page of $resource instances.
     *
     * @param class-string $resource
     *
     * @psalm-assert-if-true class-string<T> $data
     */
    public function supportsPage(string $resource, mixed $data): bool;

    /**
     * Transform the given data into a page of resource instances.
     *
     * @param int<1, max> $page
     * @param int<1, max> $itemsPerPage
     *
     * @return Page<T>
     *
     * @throws FailedTransformationException if failed to transform the given data
     */
    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page;
}
