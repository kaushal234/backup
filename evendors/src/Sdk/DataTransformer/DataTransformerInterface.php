<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Exception\NoSupportiveResourceTransformerException;
use App\Sdk\Page;
use App\Sdk\Resource\ResourceInterface;
use Psl\Collection\AccessibleCollectionInterface;

interface DataTransformerInterface
{
    /**
     * Transform the given data into an instance of the given resource class name.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
     *
     * @return T
     *
     * @throws FailedTransformationException            if failed to transform the given data into the given model
     * @throws NoSupportiveResourceTransformerException if no {@see ResourceTransformerInterface} is found to transform arbitrary data into a resource instance
     */
    public function transform(string $resource, mixed $data): ResourceInterface;

    /**
     * Transform the given data into a collection of instances of the given resource class name.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
     *
     * @return AccessibleCollectionInterface<int, T>
     *
     * @throws FailedTransformationException            if failed to transform the given data into the given model
     * @throws NoSupportiveResourceTransformerException if no {@see ResourceTransformerInterface} is found to transform arbitrary data into a resource instances collection
     */
    public function transformCollection(string $resource, mixed $data): AccessibleCollectionInterface;

    /**
     * Transform the given data into a page of the given resource class name.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
     * @param int<1, max>     $page
     * @param int<1, max>     $itemsPerPage
     *
     * @return Page<T>
     *
     * @throws FailedTransformationException            if failed to transform the given data into the given model
     * @throws NoSupportiveResourceTransformerException if no {@see ResourceTransformerInterface} is found to transform arbitrary data into a resource instances collection
     */
    public function transformPage(string $resource, mixed $data, int $page, int $itemsPerPage): Page;
}
