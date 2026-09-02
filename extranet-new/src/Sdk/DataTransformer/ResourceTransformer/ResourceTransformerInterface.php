<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\ResourceInterface;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T of ResourceInterface
 */
#[AutoconfigureTag(name: 'resource.data_transformer')]
interface ResourceTransformerInterface
{
    /**
     * @param class-string $resource
     */
    public function supports(string $resource, mixed $data): bool;

    /**
     * @return T
     *
     * @throws FailedTransformationException
     */
    public function transform(mixed $data): ResourceInterface;

    /**
     * @param class-string $resource
     */
    public function supportsCollection(string $resource, mixed $data): bool;

    /**
     * @return AccessibleCollectionInterface<T>
     *
     * @throws FailedTransformationException
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface;

    /**
     * Return true if this transformer is capable of converting $data into a page of $resource instances.
     *
     * @param class-string $resource
     *
     * @psalm-assert-if-true class-string<T> $resource
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
