<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Exception\NoSupportiveResourceTransformerException;
use App\Sdk\Page;
use App\Sdk\Resource\ResourceInterface;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class DataTransformer
{
    /**
     * @param iterable<ResourceTransformerInterface> $dataTransformers
     */
    public function __construct(
        #[AutowireIterator('resource.data_transformer')] private readonly iterable $dataTransformers = [],
    ) {
    }

    /**
     * Transform the given data into an instance of the given model class name.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
     *
     * @return T
     *
     * @throws FailedTransformationException
     * @throws NoSupportiveResourceTransformerException
     */
    public function transform(string $resource, mixed $data): ResourceInterface
    {
        foreach ($this->dataTransformers as $dataTransformer) {
            if ($dataTransformer->supports($resource, $data)) {
                /* @var T */
                return $dataTransformer->transform($data);
            }
        }

        throw NoSupportiveResourceTransformerException::forResource($resource);
    }

    /**
     * Transform the given data into an instance of the given model class name.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
     *
     * @return AccessibleCollectionInterface<int, T>
     *
     * @throws FailedTransformationException
     * @throws NoSupportiveResourceTransformerException
     */
    public function transformCollection(string $resource, mixed $data): AccessibleCollectionInterface
    {
        foreach ($this->dataTransformers as $dataTransformer) {
            if ($dataTransformer->supportsCollection($resource, $data)) {
                /* @var AccessibleCollectionInterface<int, T> */
                return $dataTransformer->transformCollection($data);
            }
        }

        throw NoSupportiveResourceTransformerException::forResource($resource);
    }

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
    public function transformPage(string $resource, mixed $data, int $page, int $itemsPerPage): Page
    {
        foreach ($this->dataTransformers as $dataTransformer) {
            if ($dataTransformer->supportsPage($resource, $data)) {
                return $dataTransformer->transformPage($data, $page, $itemsPerPage);
            }
        }

        throw NoSupportiveResourceTransformerException::forResource($resource);
    }
}
