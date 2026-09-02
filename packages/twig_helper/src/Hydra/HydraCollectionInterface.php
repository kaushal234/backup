<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Hydra;

use Alvest\TwigHelper\Model\ApiDataInterface;
use IteratorAggregate;

interface HydraCollectionInterface extends IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @param array<ApiDataInterface> $data
     */
    public function __construct(array $data);

    /**
     * @return array<ApiDataInterface>
     */
    public function getSimpleArrayCopy(): array;

    /**
     * @return array<ApiDataInterface>
     */
    public function getIndexedCollection(string $key): array;

    public function first(): ?ApiDataInterface;

    public function last(): ?ApiDataInterface;

    /**
     * @return array<ApiDataInterface>
     */
    public function all(): array;
}
